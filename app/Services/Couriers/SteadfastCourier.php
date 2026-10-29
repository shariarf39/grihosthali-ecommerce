<?php

namespace App\Services\Couriers;

use App\Models\Order;
use App\Models\Shipment;

/** Steadfast Courier (packzy) — https://portal.packzy.com/api/v1 */
class SteadfastCourier extends CourierDriver
{
    private const BASE = 'https://portal.packzy.com/api/v1';

    public function slug(): string
    {
        return 'steadfast';
    }

    public function label(): string
    {
        return 'Steadfast';
    }

    public function fields(): array
    {
        return [
            'api_key' => ['label' => 'API Key', 'required' => true, 'secret' => true],
            'secret_key' => ['label' => 'Secret Key', 'required' => true, 'secret' => true],
            'delivery_type' => ['label' => 'Delivery Type', 'type' => 'select', 'options' => ['0' => 'Home delivery', '1' => 'Point delivery (hub pickup)'], 'default' => '0'],
        ];
    }

    private function client()
    {
        return $this->http()->baseUrl(self::BASE)->withHeaders([
            'Api-Key' => (string) $this->config('api_key'),
            'Secret-Key' => (string) $this->config('secret_key'),
        ]);
    }

    public function testConnection(): array
    {
        $res = $this->client()->get('/get_balance');
        if (! $res->successful() || (int) $res->json('status') !== 200) {
            return ['ok' => false, 'message' => 'Steadfast rejected the credentials: ' . ($res->json('message') ?: $res->reason())];
        }

        return ['ok' => true, 'message' => 'Connected to Steadfast. Current balance: ' . taka($res->json('current_balance', 0))];
    }

    public function createShipment(Order $order, array $options): array
    {
        $payload = [
            'invoice' => $order->order_number,
            'recipient_name' => $order->name,
            'recipient_phone' => normalize_phone($order->phone),
            'recipient_address' => mb_substr($order->address, 0, 250),
            'cod_amount' => $this->codAmount($order, $options),
            'note' => $options['note'] ?? $order->note,
            'item_description' => $this->itemDescription($order),
            'total_lot' => (int) $order->items->sum('quantity'),
            'delivery_type' => (int) $this->config('delivery_type'),
        ];
        if ($order->email) {
            $payload['recipient_email'] = $order->email;
        }

        $res = $this->client()->post('/create_order', $payload);
        if (! $res->successful() || (int) $res->json('status') !== 200) {
            $this->fail($res, 'order creation');
        }

        return [
            'consignment_id' => (string) $res->json('consignment.consignment_id'),
            'tracking_code' => $res->json('consignment.tracking_code'),
            'status' => $res->json('consignment.status', 'in_review'),
            'delivery_fee' => null,
            'raw' => $res->json(),
        ] + ['request' => $payload];
    }

    public function fetchStatus(Shipment $shipment): ?string
    {
        $res = $this->client()->get('/status_by_cid/' . urlencode($shipment->consignment_id));

        return $res->successful() ? $res->json('delivery_status') : null;
    }

    public function trackingUrl(Shipment $shipment): ?string
    {
        return $shipment->tracking_code ? 'https://steadfast.com.bd/t/' . $shipment->tracking_code : null;
    }

    public function mapOrderStatus(string $status): ?string
    {
        return match ($status) {
            'delivered', 'partial_delivered' => 'delivered',
            'cancelled' => 'returned',
            default => null,
        };
    }
}
