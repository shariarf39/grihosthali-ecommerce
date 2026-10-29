<?php

namespace App\Services\Couriers;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

/**
 * Base class for Bangladeshi courier APIs. Credentials live in settings as
 * courier_{slug}_{field}; secret fields are stored encrypted.
 */
abstract class CourierDriver
{
    abstract public function slug(): string;

    abstract public function label(): string;

    /**
     * Credential / option fields for the admin form.
     *
     * @return array<string, array{label: string, type?: string, secret?: bool, hint?: string, options?: array, default?: string}>
     */
    abstract public function fields(): array;

    /** @return array{ok: bool, message: string, stores?: array} */
    abstract public function testConnection(): array;

    /**
     * Book a parcel. Throws CourierException on failure.
     *
     * @return array{consignment_id: string, tracking_code?: ?string, status: string, delivery_fee?: ?float, raw: array}
     */
    abstract public function createShipment(Order $order, array $options): array;

    /** Latest courier-side status for a shipment, or null if it could not be fetched. */
    abstract public function fetchStatus(Shipment $shipment): ?string;

    public function trackingUrl(Shipment $shipment): ?string
    {
        return null;
    }

    public function supportsLocations(): bool
    {
        return false;
    }

    public function supportsCancel(): bool
    {
        return false;
    }

    public function cancel(Shipment $shipment, string $reason): void
    {
        throw new CourierException($this->label() . ' does not support cancelling from the API. Please cancel from the courier panel.');
    }

    /** Location lookups (Pathao / CarryBee): list of [id, name]. */
    public function cities(): array
    {
        return [];
    }

    public function zones(int $cityId): array
    {
        return [];
    }

    public function areas(int $zoneId, ?int $cityId = null): array
    {
        return [];
    }

    /** Translate a courier status into our order status (null = leave the order alone). */
    public function mapOrderStatus(string $status): ?string
    {
        $s = strtolower($status);
        if (str_contains($s, 'partial')) {
            return 'delivered';
        }
        if (str_contains($s, 'deliver') && ! str_contains($s, 'fail') && ! str_contains($s, 'approval')) {
            return 'delivered';
        }
        if (str_contains($s, 'return')) {
            return 'returned';
        }

        return null;
    }

    /* ---------------------------------------------------------------- */

    public function enabled(): bool
    {
        return (bool) setting("courier_{$this->slug()}_enabled");
    }

    public function isConfigured(): bool
    {
        foreach ($this->fields() as $key => $field) {
            if (! empty($field['required']) && blank($this->config($key))) {
                return false;
            }
        }

        return true;
    }

    public function config(string $key): ?string
    {
        $field = $this->fields()[$key] ?? [];
        $full = "courier_{$this->slug()}_{$key}";

        return ! empty($field['secret']) ? secret_setting($full) : setting($full, $field['default'] ?? null);
    }

    public function isSandbox(): bool
    {
        return $this->config('mode') === 'sandbox';
    }

    protected function http(): PendingRequest
    {
        return Http::acceptJson()->asJson()->timeout(30)->connectTimeout(10);
    }

    /** Raise a readable error from a failed API response. */
    protected function fail(Response $response, string $context): never
    {
        $json = $response->json() ?? [];
        $message = Arr::get($json, 'message') ?: Arr::get($json, 'error') ?: $response->reason();
        $errors = Arr::get($json, 'errors');
        if (is_array($errors) && $errors) {
            $message .= ' — ' . collect($errors)->map(fn ($e, $f) => (is_string($f) ? "{$f}: " : '') . (is_array($e) ? implode(', ', $e) : $e))->implode('; ');
        }

        throw new CourierException("{$this->label()} {$context} failed ({$response->status()}): {$message}", $json);
    }

    /** First non-empty value among several possible response paths (APIs differ between versions). */
    protected function pick(array $data, array $paths, $default = null)
    {
        foreach ($paths as $path) {
            $value = Arr::get($data, $path);
            if ($value !== null && $value !== '' && $value !== []) {
                return $value;
            }
        }

        return $default;
    }

    protected function itemDescription(Order $order): string
    {
        return $order->items->map(fn ($i) => "{$i->product_name} x{$i->quantity}")->take(5)->implode(', ');
    }

    /** Amount the rider should collect: nothing for paid orders, otherwise the order total. */
    protected function codAmount(Order $order, array $options): float
    {
        return (float) ($options['cod_amount'] ?? ($order->payment_status === 'paid' ? 0 : $order->total));
    }
}
