<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Couriers\CourierException;
use App\Services\Couriers\CourierManager;
use Illuminate\Http\Request;
use Throwable;

class CourierController extends Controller
{
    public function __construct(private CourierManager $couriers)
    {
    }

    public function shipments(Request $request)
    {
        $shipments = Shipment::with('order:id,order_number,name,phone,status,total')
            ->when($request->courier, fn ($q, $c) => $q->where('courier', $c))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('consignment_id', 'like', "%$s%")
                ->orWhere('tracking_code', 'like', "%$s%")->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"))))
            ->latest()->paginate(25)->withQueryString();

        $statuses = Shipment::distinct()->orderBy('status')->pluck('status');
        $summary = [
            'active' => Shipment::where('is_active', true)->whereNotIn('status', ['delivered', 'partial_delivered', 'Delivered', 'cancelled'])->count(),
            'cod_pending' => (float) Shipment::where('is_active', true)->whereHas('order', fn ($q) => $q->where('payment_status', 'unpaid'))->sum('cod_amount'),
        ];

        return view('admin.couriers.shipments', compact('shipments', 'statuses', 'summary'));
    }

    /** Book one order with a courier (from the order page). */
    public function send(Request $request, Order $order)
    {
        $data = $request->validate([
            'courier' => 'required|in:' . implode(',', array_keys(CourierManager::LABELS)),
            'cod_amount' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:250',
            'city_id' => 'nullable|integer',
            'zone_id' => 'nullable|integer',
            'area_id' => 'nullable|integer',
        ]);

        try {
            $shipment = $this->couriers->book($order, $data['courier'], array_filter($data, fn ($v) => $v !== null && $v !== ''));
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "Sent to {$shipment->courier_label}. Consignment ID: {$shipment->consignment_id}");
    }

    /** Book several selected orders at once from the orders list. */
    public function bulkSend(Request $request)
    {
        $data = $request->validate([
            'courier' => 'required|in:' . implode(',', array_keys(CourierManager::LABELS)),
            'orders' => 'required|array|min:1',
            'orders.*' => 'integer',
        ], ['orders.required' => 'Select at least one order.']);

        $ok = 0;
        $errors = [];
        foreach (Order::whereIn('id', $data['orders'])->get() as $order) {
            try {
                $this->couriers->book($order, $data['courier']);
                $ok++;
            } catch (Throwable $e) {
                $errors[] = "#{$order->order_number}: " . \Illuminate\Support\Str::limit($e->getMessage(), 140);
            }
        }

        $label = CourierManager::LABELS[$data['courier']];
        $redirect = back()->with('success', "{$ok} order(s) sent to {$label}.");

        return $errors ? $redirect->with('error', implode(' | ', array_slice($errors, 0, 5))) : $redirect;
    }

    public function sync(Shipment $shipment)
    {
        try {
            $status = $this->couriers->sync($shipment);
        } catch (Throwable $e) {
            return back()->with('error', 'Could not fetch status: ' . $e->getMessage());
        }

        return back()->with($status ? 'success' : 'error', $status ? "Status updated: {$shipment->fresh()->status_label}" : 'The courier did not return a status.');
    }

    public function syncAll()
    {
        $count = 0;
        Shipment::with('order')->where('is_active', true)
            ->whereNotIn('status', ['delivered', 'Delivered', 'partial_delivered', 'cancelled', 'Cancelled', 'Return', 'returned'])
            ->latest()->take(100)->get()
            ->each(function ($shipment) use (&$count) {
                try {
                    if ($this->couriers->sync($shipment)) {
                        $count++;
                    }
                } catch (Throwable) {
                    // Skip shipments whose courier is unreachable; they'll be retried next time.
                }
            });

        return back()->with('success', "Synced {$count} shipment(s) with the couriers.");
    }

    public function cancel(Request $request, Shipment $shipment)
    {
        $request->validate(['reason' => 'nullable|string|max:200']);
        try {
            $this->couriers->cancel($shipment, $request->input('reason') ?: 'Cancelled by merchant');
        } catch (CourierException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Courier booking cancelled.');
    }

    /** Remove a booking locally (e.g. it was cancelled in the courier panel) so the order can be re-sent. */
    public function detach(Shipment $shipment)
    {
        $shipment->update(['is_active' => false]);

        return back()->with('success', 'Booking detached. You can send this order to a courier again.');
    }

    /** City / zone / area dropdown data for Pathao & CarryBee. */
    public function locations(Request $request, string $slug)
    {
        $driver = $this->couriers->driver($slug);
        try {
            $items = match ($request->type) {
                'zones' => $driver->zones((int) $request->city),
                'areas' => $driver->areas((int) $request->zone, (int) $request->city),
                default => $driver->cities(),
            };
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($items);
    }
}
