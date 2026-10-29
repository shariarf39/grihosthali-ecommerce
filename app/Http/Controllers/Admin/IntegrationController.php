<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailLog;
use App\Models\Setting;
use App\Services\Couriers\CourierManager;
use App\Services\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class IntegrationController extends Controller
{
    public function __construct(private CourierManager $couriers)
    {
    }

    /* ---------------- Couriers ---------------- */

    public function couriers()
    {
        if (! setting('courier_webhook_token')) {
            Setting::put(['courier_webhook_token' => Str::random(40)]);
        }

        return view('admin.integrations.couriers', ['drivers' => $this->couriers->all()]);
    }

    public function updateCourier(Request $request, string $slug)
    {
        $driver = $this->couriers->driver($slug);
        $values = ["courier_{$slug}_enabled" => $request->boolean('enabled') ? '1' : '0'];

        foreach ($driver->fields() as $key => $field) {
            $input = $request->input($key);
            if (! empty($field['secret'])) {
                // Blank secret fields keep the stored value so it is never shown or overwritten by accident.
                if (filled($input)) {
                    $values["courier_{$slug}_{$key}"] = Crypt::encryptString(trim($input));
                }
            } else {
                $values["courier_{$slug}_{$key}"] = is_string($input) ? trim($input) : $input;
            }
        }
        Setting::put($values);

        if ($request->boolean('enabled') && ! $driver->isConfigured()) {
            return back()->with('error', "{$driver->label()} saved, but some required fields are empty — it won't be available until they are filled.");
        }

        return back()->with('success', "{$driver->label()} settings saved.");
    }

    public function testCourier(string $slug)
    {
        $driver = $this->couriers->driver($slug);
        if (! $driver->isConfigured() && $slug === 'steadfast') {
            return back()->with('error', 'Enter the API key and secret key first.');
        }

        try {
            $result = $driver->testConnection();
        } catch (\Throwable $e) {
            $result = ['ok' => false, 'message' => "{$driver->label()}: " . $e->getMessage()];
        }

        if (! empty($result['stores'])) {
            Setting::put(["courier_{$slug}_stores" => json_encode($result['stores'])]);
            // Pick the first store automatically if none is chosen yet.
            if (! setting("courier_{$slug}_store_id")) {
                Setting::put(["courier_{$slug}_store_id" => (string) $result['stores'][0]['id']]);
            }
        }

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function updateCourierGeneral(Request $request)
    {
        $request->validate(['courier_default' => 'nullable|in:' . implode(',', array_keys(CourierManager::LABELS))]);
        Setting::put([
            'courier_default' => $request->input('courier_default'),
            'courier_auto_status' => $request->boolean('courier_auto_status') ? '1' : '0',
        ]);

        return back()->with('success', 'Courier preferences saved.');
    }

    /* ---------------- Email ---------------- */

    public function email()
    {
        $logs = MailLog::latest()->take(8)->get();
        $stats = [
            'sent' => MailLog::where('status', 'sent')->where('created_at', '>=', now()->subDays(30))->count(),
            'failed' => MailLog::where('status', 'failed')->where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return view('admin.integrations.email', compact('logs', 'stats'));
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'mail_host' => 'nullable|string|max:150',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_encryption' => 'nullable|in:tls,ssl,none',
            'mail_username' => 'nullable|string|max:150',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string|max:100',
            'mail_admin_recipients' => 'nullable|string|max:500',
            'mail_status_list' => 'nullable|array',
        ]);

        $values = $request->only(['mail_host', 'mail_port', 'mail_encryption', 'mail_username', 'mail_from_address', 'mail_from_name', 'mail_admin_recipients']);
        $values['mail_enabled'] = $request->boolean('mail_enabled') ? '1' : '0';
        if (filled($request->input('mail_password'))) {
            // Gmail app passwords are shown with spaces; SMTP needs them without.
            $values['mail_password'] = Crypt::encryptString(str_replace(' ', '', $request->input('mail_password')));
        }
        foreach (array_keys(Notifier::EVENTS) as $event) {
            $values["mail_event_{$event}"] = $request->boolean("events.{$event}") ? '1' : '0';
        }
        $values['mail_status_list'] = json_encode(array_values($request->input('mail_status_list', [])));
        Setting::put($values);

        return back()->with('success', 'Email settings saved.');
    }

    public function testEmail(Request $request, Notifier $notifier)
    {
        $request->validate(['test_to' => 'required|email']);
        $error = $notifier->sendTest($request->test_to);

        return back()->with($error ? 'error' : 'success', $error
            ? 'Test email failed: ' . \Illuminate\Support\Str::limit($error, 300)
            : "Test email sent to {$request->test_to}. Check the inbox (and spam folder).");
    }

    public function mailLogs(Request $request)
    {
        $logs = MailLog::when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('to', 'like', "%$s%")->orWhere('subject', 'like', "%$s%")))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.integrations.mail-logs', compact('logs'));
    }
}
