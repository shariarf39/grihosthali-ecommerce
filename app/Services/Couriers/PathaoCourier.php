<?php

namespace App\Services\Couriers;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\Cache;

/** Pathao Courier merchant API (Aladdin) — OAuth password grant + Bearer token. */
class PathaoCourier extends CourierDriver
{
    public function slug(): string
    {
        return 'pathao';
    }

    public function label(): string
    {
        return 'Pathao';
    }

    public function fields(): array
    {
        return [
            'mode' => ['label' => 'Environment', 'type' => 'select', 'options' => ['live' => 'Live', 'sandbox' => 'Sandbox (testing)'], 'default' => 'live'],
            'client_id' => ['label' => 'Client ID', 'required' => true],
            'client_secret' => ['label' => 'Client Secret', 'required' => true, 'secret' => true],
            'username' => ['label' => 'Merchant Email (username)', 'required' => true],
            'password' => ['label' => 'Merchant Password', 'required' => true, 'secret' => true],
            'store_id' => ['label' => 'Pickup Store', 'type' => 'store', 'required' => true, 'hint' => 'Click “Test connection” to load your Pathao stores.'],
            'delivery_type' => ['label' => 'Delivery Type', 'type' => 'select', 'options' => ['48' => 'Normal delivery', '12' => 'On-demand delivery'], 'default' => '48'],
            'item_weight' => ['label' => 'Default Weight (kg)', 'default' => '0.5', 'hint' => 'Between 0.5 and 10 kg'],
        ];
    }

    private function baseUrl(): string
    {
        return $this->isSandbox() ? 'https://courier-api-sandbox.pathao.com' : 'https://api-hermes.pathao.com';
    }

    /** Access token, cached until shortly before it expires. */
    private function token(bool $fresh = false): string
    {
        $key = 'pathao_token_' . md5($this->baseUrl() . $this->config('client_id') . $this->config('username'));
        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addMinutes(50), function () {
            $res = $this->http()->baseUrl($this->baseUrl())->post('/aladdin/api/v1/issue-token', [
                'client_id' => $this->config('client_id'),
                'client_secret' => $this->config('client_secret'),
                'username' => $this->config('username'),
                'password' => $this->config('password'),
                'grant_type' => 'password',
            ]);
            if (! $res->successful() || ! $res->json('access_token')) {
                $this->fail($res, 'login');
            }

            return $res->json('access_token');
        });
    }

    private function client()
    {
        return $this->http()->baseUrl($this->baseUrl())->withToken($this->token());
    }

    private function list(string $path, string $idKey, string $nameKey): array
    {
        $res = $this->client()->get($path);
        if (! $res->successful()) {
            $this->fail($res, 'lookup');
        }

        return collect($this->pick($res->json(), ['data.data', 'data'], []))
            ->map(fn ($row) => ['id' => $row[$idKey] ?? null, 'name' => $row[$nameKey] ?? ''])
            ->filter(fn ($r) => $r['id'])->values()->all();
    }

    public function testConnection(): array
    {
        try {
            $this->token(true);
            $stores = $this->list('/aladdin/api/v1/stores', 'store_id', 'store_name');
        } catch (CourierException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }

        return ['ok' => true, 'message' => 'Connected to Pathao (' . ($this->isSandbox() ? 'sandbox' : 'live') . '). ' . count($stores) . ' store(s) found.', 'stores' => $stores];
    }

    public function supportsLocations(): bool
    {
        return true;
    }

    public function cities(): array
    {
        return Cache::remember('pathao_cities_' . $this->config('mode'), now()->addDay(), fn () => $this->list('/aladdin/api/v1/city-list', 'city_id', 'city_name'));
    }

    public function zones(int $cityId): array
    {
        return Cache::remember("pathao_zones_{$cityId}_" . $this->config('mode'), now()->addDay(), fn () => $this->list("/aladdin/api/v1/cities/{$cityId}/zone-list", 'zone_id', 'zone_name'));
    }

    public function areas(int $zoneId, ?int $cityId = null): array
    {
        return Cache::remember("pathao_areas_{$zoneId}_" . $this->config('mode'), now()->addDay(), fn () => $this->list("/aladdin/api/v1/zones/{$zoneId}/area-list", 'area_id', 'area_name'));
    }

    public function createShipment(Order $order, array $options): array
    {
        $payload = array_filter([
            'store_id' => (int) $this->config('store_id'),
            'merchant_order_id' => $order->order_number,
            'recipient_name' => $order->name,
            'recipient_phone' => normalize_phone($order->phone),
            'recipient_address' => mb_substr($order->address, 0, 220),
            'recipient_city' => ! empty($options['city_id']) ? (int) $options['city_id'] : null,
            'recipient_zone' => ! empty($options['zone_id']) ? (int) $options['zone_id'] : null,
            'recipient_area' => ! empty($options['area_id']) ? (int) $options['area_id'] : null,
            'delivery_type' => (int) $this->config('delivery_type'),
            'item_type' => 2,
            'special_instruction' => $options['note'] ?? $order->note,
            'item_quantity' => (int) $order->items->sum('quantity'),
            'item_weight' => (float) ($options['weight'] ?? $this->config('item_weight') ?: 0.5),
            'amount_to_collect' => (int) round($this->codAmount($order, $options)),
            'item_description' => $this->itemDescription($order),
        ], fn ($v) => $v !== null && $v !== '');
        $payload['amount_to_collect'] = (int) round($this->codAmount($order, $options));

        $res = $this->client()->post('/aladdin/api/v1/orders', $payload);
        if ($res->status() === 401) {
            $this->token(true);
            $res = $this->client()->post('/aladdin/api/v1/orders', $payload);
        }
        if (! $res->successful() || ! $res->json('data.consignment_id')) {
            $this->fail($res, 'order creation');
        }

        return [
            'consignment_id' => (string) $res->json('data.consignment_id'),
            'tracking_code' => (string) $res->json('data.consignment_id'),
            'status' => $res->json('data.order_status', 'Pending'),
            'delivery_fee' => $res->json('data.delivery_fee'),
            'raw' => $res->json(),
            'request' => $payload,
        ];
    }

    public function fetchStatus(Shipment $shipment): ?string
    {
        foreach (["/aladdin/api/v1/orders/{$shipment->consignment_id}/info", "/aladdin/api/v1/orders/{$shipment->consignment_id}"] as $path) {
            $res = $this->client()->get($path);
            if ($res->successful()) {
                return $this->pick($res->json(), ['data.order_status', 'data.order_status_slug', 'data.data.order_status']);
            }
        }

        return null;
    }

    public function trackingUrl(Shipment $shipment): ?string
    {
        return 'https://merchant.pathao.com/tracking?consignment_id=' . urlencode($shipment->consignment_id)
            . '&phone=' . urlencode(normalize_phone($shipment->order?->phone));
    }
}
