<?php

namespace App\Services\Couriers;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\Cache;

/** CarryBee merchant API v2 — Client-ID / Client-Secret / Client-Context headers. */
class CarrybeeCourier extends CourierDriver
{
    public function slug(): string
    {
        return 'carrybee';
    }

    public function label(): string
    {
        return 'CarryBee';
    }

    public function fields(): array
    {
        return [
            'mode' => ['label' => 'Environment', 'type' => 'select', 'options' => ['live' => 'Live', 'sandbox' => 'Sandbox (testing)'], 'default' => 'live'],
            'client_id' => ['label' => 'Client ID', 'required' => true],
            'client_secret' => ['label' => 'Client Secret', 'required' => true, 'secret' => true],
            'client_context' => ['label' => 'Client Context', 'required' => true, 'secret' => true],
            'store_id' => ['label' => 'Pickup Store', 'type' => 'store', 'required' => true, 'hint' => 'Click “Test connection” to load your CarryBee stores.'],
            'delivery_type' => ['label' => 'Delivery Type', 'type' => 'select', 'options' => ['1' => 'Normal delivery', '2' => 'Express delivery'], 'default' => '1'],
            'item_weight' => ['label' => 'Default Weight (grams)', 'default' => '500'],
        ];
    }

    private function client()
    {
        return $this->http()
            ->baseUrl($this->isSandbox() ? 'https://stage-sandbox.carrybee.com' : 'https://developers.carrybee.com')
            ->withHeaders([
                'Client-ID' => (string) $this->config('client_id'),
                'Client-Secret' => (string) $this->config('client_secret'),
                'Client-Context' => (string) $this->config('client_context'),
            ]);
    }

    private function list(string $path, array $listPaths): array
    {
        $res = $this->client()->get($path);
        if (! $res->successful() || $res->json('error')) {
            $this->fail($res, 'lookup');
        }

        return collect($this->pick($res->json(), $listPaths, []))
            ->map(fn ($row) => ['id' => $row['id'] ?? $row['store_id'] ?? null, 'name' => $row['name'] ?? $row['store_name'] ?? ''])
            ->filter(fn ($r) => $r['id'])->values()->all();
    }

    public function testConnection(): array
    {
        try {
            $stores = $this->list('/api/v2/stores', ['data.stores', 'stores', 'data']);
        } catch (CourierException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }

        return ['ok' => true, 'message' => 'Connected to CarryBee (' . ($this->isSandbox() ? 'sandbox' : 'live') . '). ' . count($stores) . ' store(s) found.', 'stores' => $stores];
    }

    public function supportsLocations(): bool
    {
        return true;
    }

    public function supportsCancel(): bool
    {
        return true;
    }

    public function cities(): array
    {
        return Cache::remember('carrybee_cities_' . $this->config('mode'), now()->addDay(), fn () => $this->list('/api/v2/cities', ['data.cities', 'cities', 'data']));
    }

    public function zones(int $cityId): array
    {
        return Cache::remember("carrybee_zones_{$cityId}_" . $this->config('mode'), now()->addDay(), fn () => $this->list("/api/v2/cities/{$cityId}/zones", ['data.zones', 'zones', 'data']));
    }

    public function areas(int $zoneId, ?int $cityId = null): array
    {
        return Cache::remember("carrybee_areas_{$cityId}_{$zoneId}_" . $this->config('mode'), now()->addDay(), fn () => $this->list("/api/v2/cities/{$cityId}/zones/{$zoneId}/areas", ['data.areas', 'areas', 'data']));
    }

    public function createShipment(Order $order, array $options): array
    {
        $cityId = $options['city_id'] ?? null;
        $zoneId = $options['zone_id'] ?? null;

        // CarryBee needs city + zone; ask its address parser when the admin did not pick them.
        if (! $cityId || ! $zoneId) {
            $lookup = $this->client()->post('/api/v2/address-details', ['query' => $order->address]);
            $cityId = $cityId ?: $lookup->json('data.city_id');
            $zoneId = $zoneId ?: $lookup->json('data.zone_id');
            if (! $cityId || ! $zoneId) {
                throw new CourierException('CarryBee could not detect the city/zone from the address. Please select City and Zone.');
            }
        }

        $payload = array_filter([
            'store_id' => (string) $this->config('store_id'),
            'merchant_order_id' => $order->order_number,
            'delivery_type' => (int) $this->config('delivery_type'),
            'product_type' => 1,
            'recipient_phone' => normalize_phone($order->phone),
            'recipient_name' => $order->name,
            'recipient_address' => mb_substr($order->address, 0, 250),
            'city_id' => (int) $cityId,
            'zone_id' => (int) $zoneId,
            'area_id' => ! empty($options['area_id']) ? (int) $options['area_id'] : null,
            'special_instruction' => $options['note'] ?? $order->note,
            'product_description' => $this->itemDescription($order),
            'item_weight' => (int) ($options['weight'] ?? $this->config('item_weight') ?: 500),
            'item_quantity' => (int) $order->items->sum('quantity'),
        ], fn ($v) => $v !== null && $v !== '');
        $payload['collectable_amount'] = (int) round($this->codAmount($order, $options));

        $res = $this->client()->post('/api/v2/orders', $payload);
        $consignment = $this->pick($res->json() ?? [], ['data.order.consignment_id', 'data.consignment_id']);
        if (! $res->successful() || $res->json('error') || ! $consignment) {
            $this->fail($res, 'order creation');
        }

        return [
            'consignment_id' => (string) $consignment,
            'tracking_code' => (string) $consignment,
            'status' => 'pending',
            'delivery_fee' => $this->pick($res->json(), ['data.order.delivery_fee', 'data.delivery_fee']),
            'raw' => $res->json(),
            'request' => $payload,
        ];
    }

    public function fetchStatus(Shipment $shipment): ?string
    {
        $res = $this->client()->get('/api/v2/orders/' . urlencode($shipment->consignment_id) . '/details');
        if (! $res->successful()) {
            return null;
        }

        return $this->pick($res->json(), ['data.order_status', 'data.status', 'data.order.status', 'data.transfer_status']);
    }

    public function cancel(Shipment $shipment, string $reason): void
    {
        $res = $this->client()->post('/api/v2/orders/' . urlencode($shipment->consignment_id) . '/cancel', ['cancellation_reason' => $reason]);
        if (! $res->successful() || $res->json('error')) {
            $this->fail($res, 'cancellation');
        }
    }
}
