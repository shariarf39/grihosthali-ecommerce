<?php

namespace App\Services;

use App\Mail\StoreMail;
use App\Models\ContactMessage;
use App\Models\MailLog;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends store emails through the SMTP account configured in Admin → Integrations → Email.
 * Every email type can be switched on/off; mail is sent after the HTTP response so customers never wait.
 */
class Notifier
{
    /** Email type => [label, audience, default on?] */
    public const EVENTS = [
        'order_placed_customer' => ['Order confirmation', 'Customer', true],
        'order_status_customer' => ['Order status updates', 'Customer', true],
        'return_update_customer' => ['Return / refund updates', 'Customer', true],
        'welcome_customer' => ['Welcome email after registration', 'Customer', true],
        'order_placed_admin' => ['New order alert', 'Admin', true],
        'return_request_admin' => ['New return request alert', 'Admin', true],
        'contact_admin' => ['New contact message alert', 'Admin', true],
        'low_stock_admin' => ['Low stock alert', 'Admin', true],
    ];

    /** Order statuses that trigger a customer email by default. */
    public const DEFAULT_STATUSES = ['confirmed', 'shipped', 'delivered', 'cancelled'];

    public function enabled(): bool
    {
        return (bool) setting('mail_enabled') && setting('mail_host') && setting('mail_from_address');
    }

    public function eventEnabled(string $event): bool
    {
        return $this->enabled() && (bool) setting("mail_event_{$event}", self::EVENTS[$event][2] ? '1' : '0');
    }

    public function adminRecipients(): array
    {
        $list = setting('mail_admin_recipients') ?: setting('email');

        return array_values(array_filter(array_map('trim', preg_split('/[,;\s]+/', (string) $list)), fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));
    }

    /* ---------------- Events ---------------- */

    public function orderPlaced(Order $order): void
    {
        $order->loadMissing('items');
        if ($order->email) {
            $this->queue('order_placed_customer', $order->email, "Order confirmed — #{$order->order_number}", 'emails.order-placed', compact('order'));
        }
        foreach ($this->adminRecipients() as $to) {
            $this->queue('order_placed_admin', $to, "🛒 New order #{$order->order_number} — " . taka($order->total), 'emails.admin-order', compact('order'));
        }
    }

    public function orderStatusChanged(Order $order): void
    {
        $statuses = json_decode((string) setting('mail_status_list'), true) ?: self::DEFAULT_STATUSES;
        if ($order->email && in_array($order->status, $statuses, true)) {
            $order->loadMissing('items', 'activeShipment');
            $this->queue('order_status_customer', $order->email, "Your order #{$order->order_number} is {$order->status_label}", 'emails.order-status', compact('order'));
        }
    }

    public function returnRequested(OrderReturn $return): void
    {
        $return->loadMissing('items', 'order');
        foreach ($this->adminRecipients() as $to) {
            $this->queue('return_request_admin', $to, "↩ Return request #{$return->return_number} (order #{$return->order->order_number})", 'emails.admin-return', compact('return'));
        }
    }

    public function returnStatusChanged(OrderReturn $return): void
    {
        $return->loadMissing('items', 'order');
        if ($return->order->email) {
            $this->queue('return_update_customer', $return->order->email, "Return #{$return->return_number}: {$return->status_label}", 'emails.return-status', compact('return'));
        }
    }

    public function contactReceived(ContactMessage $message): void
    {
        foreach ($this->adminRecipients() as $to) {
            $this->queue('contact_admin', $to, 'New message: ' . ($message->subject ?: $message->name), 'emails.admin-contact', compact('message'));
        }
    }

    public function welcome(User $user): void
    {
        $this->queue('welcome_customer', $user->email, 'Welcome to ' . setting('site_name'), 'emails.welcome', compact('user'));
    }

    public function lowStock(Product $product): void
    {
        foreach ($this->adminRecipients() as $to) {
            $this->queue('low_stock_admin', $to, "⚠ Low stock: {$product->name} ({$product->stock} left)", 'emails.admin-low-stock', compact('product'));
        }
    }

    /** Immediate send used by the "Send test email" button; returns an error message or null. */
    public function sendTest(string $to): ?string
    {
        if (! setting('mail_host') || ! setting('mail_from_address')) {
            return 'Fill in the SMTP host and From address first.';
        }

        return $this->deliver('test', $to, 'Test email from ' . setting('site_name'), 'emails.test', []);
    }

    /* ---------------- Transport ---------------- */

    private function queue(string $event, string $to, string $subject, string $view, array $data): void
    {
        if (! $this->eventEnabled($event)) {
            return;
        }
        dispatch(fn () => $this->deliver($event, $to, $subject, $view, $data))->afterResponse();
    }

    private function deliver(string $event, string $to, string $subject, string $view, array $data): ?string
    {
        try {
            $this->configureMailer();
            Mail::mailer('store_smtp')->to($to)->send(new StoreMail($subject, $view, $data));
            MailLog::create(['event' => $event, 'to' => $to, 'subject' => $subject, 'status' => 'sent']);

            return null;
        } catch (Throwable $e) {
            MailLog::create(['event' => $event, 'to' => $to, 'subject' => $subject, 'status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 1000)]);

            return $e->getMessage();
        }
    }

    /** Build an SMTP mailer from admin settings (overrides .env so no server access is needed). */
    private function configureMailer(): void
    {
        $encryption = setting('mail_encryption', 'tls');
        config([
            'mail.mailers.store_smtp' => [
                'transport' => 'smtp',
                'scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
                'host' => setting('mail_host'),
                'port' => (int) setting('mail_port', 587),
                'username' => setting('mail_username'),
                'password' => secret_setting('mail_password'),
                'timeout' => 20,
            ],
            'mail.from' => [
                'address' => setting('mail_from_address'),
                'name' => setting('mail_from_name', setting('site_name')),
            ],
        ]);
        Mail::purge('store_smtp');
    }
}
