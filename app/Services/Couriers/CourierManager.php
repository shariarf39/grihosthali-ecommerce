<?php

namespace App\Services\Couriers;

use App\Models\CrmActivity;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CourierManager
{
    public const LABELS = ['steadfast' => 'Steadfast', 'pathao' => 'Pathao', 'carrybee' => 'CarryBee'];

    private const DRIVERS = [
        'steadfast' => SteadfastCourier::class,
        'pathao' => PathaoCourier::class,
        'carrybee' => CarrybeeCourier::class,
    ];

    private array $instances = [];

    public function driver(string $slug): CourierDriver
    {
        if (! isset(self::DRIVERS[$slug])) {
            throw new InvalidArgumentException("Unknown courier [{$slug}]");
        }

        return $this->instances[$slug] ??= new (self::DRIVERS[$slug]);
    }

    /** @return CourierDriver[] */
    public function all(): array
    {
        return array_map(fn ($slug) => $this->driver($slug), array_keys(self::DRIVERS));
    }

    /** Couriers switched on and fully configured, ready to book parcels. */
    public function available(): array
    {
        return array_values(array_filter($this->all(), fn ($d) => $d->enabled() && $d->isConfigured()));
    }

    public function book(Order $order, string $slug, array $options = []): Shipment
    {
        if ($order->activeShipment) {
            throw new CourierException("Order #{$order->order_number} is already booked with {$order->activeShipment->courier_label} ({$order->activeShipment->consignment_id}).");
        }
        $driver = $this->driver($slug);
        if (! $driver->enabled() || ! $driver->isConfigured()) {
            throw new CourierException("{$driver->label()} is not enabled or configured. Go to Integrations → Couriers.");
        }

        $order->loadMissing('items');
        $result = $driver->createShipment($order, $options);

        return DB::transaction(function () use ($order, $slug, $result, $options) {
            $shipment = $order->shipments()->create([
                'courier' => $slug,
                'consignment_id' => $result['consignment_id'],
                'tracking_code' => $result['tracking_code'] ?? null,
                'status' => $result['status'] ?? 'pending',
                'cod_amount' => $result['request']['cod_amount'] ?? $result['request']['amount_to_collect'] ?? $result['request']['collectable_amount'] ?? 0,
                'delivery_fee' => $result['delivery_fee'] ?? null,
                'request' => $result['request'] ?? $options,
                'response' => $result['raw'] ?? [],
                'last_synced_at' => now(),
                'user_id' => auth()->id(),
            ]);

            if (in_array($order->status, ['pending', 'confirmed', 'processing'])) {
                $order->update(['status' => 'shipped']);
            }
            $this->note($order, "Booked with {$shipment->courier_label} — consignment {$shipment->consignment_id}");

            return $shipment;
        });
    }

    /** Pull the latest status from the courier and mirror it onto the order. */
    public function sync(Shipment $shipment): ?string
    {
        $status = $this->driver($shipment->courier)->fetchStatus($shipment);
        if ($status) {
            $this->applyStatus($shipment, $status);
        }

        return $status;
    }

    public function applyStatus(Shipment $shipment, string $status): void
    {
        $changed = $shipment->status !== $status;
        $shipment->update(['status' => $status, 'last_synced_at' => now()]);

        $order = $shipment->order;
        $mapped = $this->driver($shipment->courier)->mapOrderStatus($status);
        if ($changed && $mapped && setting('courier_auto_status') && $order->status !== $mapped && ! in_array($order->status, ['cancelled'])) {
            $update = ['status' => $mapped];
            if ($mapped === 'delivered') {
                $update['delivered_at'] = now();
                if ($order->payment_method === 'cod' && $order->payment_status === 'unpaid') {
                    $update['payment_status'] = 'paid';
                }
            }
            $order->update($update);
            app(\App\Services\Notifier::class)->orderStatusChanged($order);
        }
        if ($changed) {
            $this->note($order, "{$shipment->courier_label} status: " . $shipment->status_label);
        }
    }

    public function cancel(Shipment $shipment, string $reason): void
    {
        $this->driver($shipment->courier)->cancel($shipment, $reason);
        $shipment->update(['is_active' => false, 'status' => 'cancelled']);
        $this->note($shipment->order, "{$shipment->courier_label} booking {$shipment->consignment_id} cancelled: {$reason}");
    }

    private function note(Order $order, string $message): void
    {
        if ($order->crm_customer_id) {
            CrmActivity::create(['crm_customer_id' => $order->crm_customer_id, 'user_id' => auth()->id(), 'type' => 'system', 'message' => "Order #{$order->order_number}: {$message}"]);
        }
    }
}
