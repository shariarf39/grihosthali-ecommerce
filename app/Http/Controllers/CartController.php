<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private Cart $cart)
    {
    }

    public function index()
    {
        return view('cart', ['items' => $this->cart->items(), 'subtotal' => $this->cart->subtotal()]);
    }

    public function data()
    {
        return response()->json($this->cart->toArray());
    }

    public function add(Request $request)
    {
        $product = $this->product($request);
        $qty = max(1, (int) $request->input('qty', 1));

        if ($product->stock < $this->cart->quantity($product->id) + $qty) {
            return response()->json(['message' => 'Sorry, not enough stock available.'] + $this->cart->toArray(), 422);
        }
        $this->cart->add($product->id, $qty);

        return response()->json(['message' => $product->name . ' added to cart.'] + $this->cart->toArray());
    }

    public function update(Request $request)
    {
        $product = $this->product($request);
        $qty = (int) $request->input('qty', 0);

        if ($qty > $product->stock) {
            return response()->json(['message' => 'Only ' . $product->stock . ' items in stock.'] + $this->cart->toArray(), 422);
        }
        $this->cart->set($product->id, $qty);

        return response()->json($this->cart->toArray());
    }

    public function remove(Request $request)
    {
        $this->cart->set((int) $request->input('product_id'), 0);

        return response()->json(['message' => 'Item removed from cart.'] + $this->cart->toArray());
    }

    /** "Buy Now" / "Order Now": put the product in the cart and jump straight to checkout. */
    public function buyNow(Request $request)
    {
        $product = $this->product($request);
        $qty = max(1, (int) $request->input('qty', 1));
        if ($this->cart->quantity($product->id) < $qty) {
            $this->cart->set($product->id, min($qty, max(1, $product->stock)));
        }

        return redirect()->route('checkout');
    }

    private function product(Request $request): Product
    {
        $request->validate(['product_id' => 'required|integer']);

        return Product::active()->findOrFail($request->product_id);
    }
}
