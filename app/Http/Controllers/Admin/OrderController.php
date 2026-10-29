<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
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
        $order->load('items.product', 'user');

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Order::STATUSES)),
            'payment_status' => 'required|in:unpaid,paid,refunded',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($order, $data) {
            $wasCancelled = in_array($order->status, ['cancelled', 'returned']);
            $nowCancelled = in_array($data['status'], ['cancelled', 'returned']);

            // Return stock when an order is cancelled, take it again if it is reopened.
            if ($wasCancelled !== $nowCancelled) {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $sign = $nowCancelled ? 1 : -1;
                        Product::whereKey($item->product_id)->update([
                            'stock' => DB::raw('GREATEST(stock + ' . ($sign * $item->quantity) . ', 0)'),
                            'sold_count' => DB::raw('GREATEST(sold_count - ' . ($sign * $item->quantity) . ', 0)'),
                        ]);
                    }
                }
            }
            if ($data['status'] === 'delivered' && $order->payment_method === 'cod') {
                $data['payment_status'] = 'paid';
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
