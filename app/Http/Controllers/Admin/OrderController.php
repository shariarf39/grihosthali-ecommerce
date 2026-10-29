<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmActivity;
use App\Models\Order;
use App\Models\Product;
use App\Services\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::withCount('items')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('order_number', 'like', "%$s%")
                ->orWhere('phone', 'like', "%$s%")->orWhere('name', 'like', "%$s%")))
            ->when($request->from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()->paginate(20)->withQueryString();

        $counts = Order::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        return view('admin.orders.index', compact('orders', 'counts'));
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'user', 'returns');
        $customer = $order->crm_customer_id
            ? \App\Models\CrmCustomer::withStats()->find($order->crm_customer_id)
            : null;

        return view('admin.orders.show', compact('order', 'customer'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Order::STATUSES)),
            'payment_status' => 'required|in:unpaid,paid,partially_refunded,refunded',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($order, $data) {
            // Only cancellation puts stock back. "Returned" stock comes back through the Returns module,
            // where each item is inspected (resellable vs damaged) before restocking.
            $wasCancelled = $order->status === 'cancelled';
            $nowCancelled = $data['status'] === 'cancelled';

            if ($wasCancelled !== $nowCancelled) {
                foreach ($order->items as $item) {
                    if (! $item->product_id) {
                        continue;
                    }
                    Inventory::move($item->product_id, $nowCancelled ? $item->quantity : -$item->quantity,
                        $nowCancelled ? 'cancel_restore' : 'reopen', $order->order_number);
                    Product::whereKey($item->product_id)->update([
                        'sold_count' => DB::raw('GREATEST(sold_count ' . ($nowCancelled ? '-' : '+') . ' ' . (int) $item->quantity . ', 0)'),
                    ]);
                }
            }
            if ($data['status'] === 'delivered') {
                $data['delivered_at'] = $order->delivered_at ?? now();
                if ($order->payment_method === 'cod' && $data['payment_status'] === 'unpaid') {
                    $data['payment_status'] = 'paid';
                }
            }

            if ($order->status !== $data['status'] && $order->crm_customer_id) {
                CrmActivity::create([
                    'crm_customer_id' => $order->crm_customer_id,
                    'user_id' => auth()->id(),
                    'type' => 'system',
                    'message' => "Order #{$order->order_number} status changed: " . Order::STATUSES[$order->status] . ' → ' . Order::STATUSES[$data['status']],
                ]);
            }
            $order->update($data);
        });

        return back()->with('success', 'Order updated.');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }

    public function invoice(Order $order)
    {
        $order->load('items');

        return view('admin.orders.invoice', compact('order'));
    }
}
