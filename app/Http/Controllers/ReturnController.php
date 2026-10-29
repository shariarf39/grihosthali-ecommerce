<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Returns;
use Illuminate\Http\Request;

/** Customer-side return requests. Access: order owner, or a session that verified order number + phone. */
class ReturnController extends Controller
{
    public function create(string $orderNumber)
    {
        $order = $this->authorizedOrder($orderNumber);
        if (! $order->isReturnable()) {
            return redirect()->route('track-order')->with('error', 'This order is not eligible for return. Returns are accepted within ' . setting('return_days', 7) . ' days of delivery.');
        }
        $returned = $order->returnedQuantities();

        return view('returns.create', compact('order', 'returned'));
    }

    public function store(Request $request, string $orderNumber)
    {
        $order = $this->authorizedOrder($orderNumber);
        abort_unless($order->isReturnable(), 403);

        $return = Returns::createFromRequest($request, $order, 'customer');
        if (is_string($return)) {
            return back()->withInput()->with('error', $return);
        }

        return redirect()->route('returns.create', $order->order_number)
            ->with('success', "Return request #{$return->return_number} submitted. We will contact you shortly.");
    }

    private function authorizedOrder(string $orderNumber): Order
    {
        $order = Order::where('order_number', $orderNumber)->with('items', 'returns.items')->firstOrFail();
        $owns = $order->user_id && $order->user_id === auth()->id();
        $verified = in_array($orderNumber, session('verified_orders', []), true) || in_array($orderNumber, session('my_orders', []), true);
        abort_unless($owns || $verified, 403, 'Please track your order first to request a return.');

        return $order;
    }
}
