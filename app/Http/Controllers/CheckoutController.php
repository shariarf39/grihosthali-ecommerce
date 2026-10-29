<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Services\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(private Cart $cart)
    {
    }

    public static function shippingRates(): array
    {
        return [
            'inside' => ['label' => 'Inside Dhaka', 'charge' => (float) setting('shipping_inside', 70)],
            'outside' => ['label' => 'Outside Dhaka', 'charge' => (float) setting('shipping_outside', 130)],
        ];
    }

    public function index()
    {
        $items = $this->cart->items();
        if (! $items) {
            return redirect()->route('home')->with('error', 'Your cart is empty.');
        }
        $subtotal = $this->cart->subtotal();
        [$coupon, $discount] = $this->currentCoupon($subtotal);

        return view('checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'rates' => self::shippingRates(),
            'freeShippingMin' => (float) setting('free_shipping_min', 0),
            'user' => auth()->user(),
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['coupon_code' => 'required|string']);
        $coupon = Coupon::where('code', strtoupper(trim($request->coupon_code)))->first();

        if (! $coupon) {
            return back()->with('error', 'Invalid coupon code.')->withInput();
        }
        if ($error = $coupon->validationError($this->cart->subtotal())) {
            return back()->with('error', $error)->withInput();
        }
        session(['coupon' => $coupon->code]);

        return back()->with('success', 'Coupon applied successfully!');
    }

    public function removeCoupon()
    {
        session()->forget('coupon');

        return back();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => ['required', 'regex:/^(\+?88)?01[3-9]\d{8}$/'],
            'email' => 'nullable|email|max:150',
            'address' => 'required|string|max:500',
            'shipping_area' => 'required|in:inside,outside',
            'note' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|in:cod',
        ], [
            'phone.regex' => 'Please enter a valid Bangladeshi mobile number (e.g. 01XXXXXXXXX).',
        ]);

        $items = $this->cart->items();
        if (! $items) {
            return redirect()->route('home')->with('error', 'Your cart is empty.');
        }

        $subtotal = $this->cart->subtotal();
        [$coupon, $discount] = $this->currentCoupon($subtotal);
        $rates = self::shippingRates();
        $freeMin = (float) setting('free_shipping_min', 0);
        $shipping = ($freeMin > 0 && $subtotal >= $freeMin) ? 0 : $rates[$data['shipping_area']]['charge'];

        $order = DB::transaction(function () use ($data, $items, $subtotal, $discount, $coupon, $shipping, $rates) {
            $order = Order::create([
                'order_number' => Order::generateNumber(),
                'user_id' => auth()->id(),
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'address' => $data['address'],
                'shipping_area' => $rates[$data['shipping_area']]['label'],
                'note' => $data['note'] ?? null,
                'subtotal' => $subtotal,
                'shipping_charge' => $shipping,
                'discount' => $discount,
                'coupon_code' => $coupon?->code,
                'total' => max(0, $subtotal + $shipping - $discount),
                'payment_method' => 'cod',
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'image' => $item['product']->image,
                    'price' => $item['product']->sell_price,
                    'quantity' => $item['qty'],
                    'total' => $item['total'],
                ]);
                Product::whereKey($item['product']->id)->update([
                    'stock' => DB::raw('GREATEST(stock - ' . (int) $item['qty'] . ', 0)'),
                    'sold_count' => DB::raw('sold_count + ' . (int) $item['qty']),
                ]);
            }
            $coupon?->increment('used');

            return $order;
        });

        $this->cart->clear();
        session()->push('my_orders', $order->order_number);

        return redirect()->route('checkout.success', $order->order_number);
    }

    public function success(string $orderNumber)
    {
        // Only the customer who placed the order (this session or their account) can view it.
        $order = Order::where('order_number', $orderNumber)->with('items')->firstOrFail();
        abort_unless(in_array($orderNumber, session('my_orders', [])) || ($order->user_id && $order->user_id === auth()->id()), 404);

        return view('checkout-success', compact('order'));
    }

    private function currentCoupon(float $subtotal): array
    {
        $code = session('coupon');
        $coupon = $code ? Coupon::where('code', $code)->first() : null;
        if (! $coupon || $coupon->validationError($subtotal)) {
            session()->forget('coupon');

            return [null, 0];
        }

        return [$coupon, $coupon->discountFor($subtotal)];
    }
}
