<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private Wishlist $wishlist)
    {
    }

    public function index()
    {
        $ids = $this->wishlist->ids();
        // Keep the order the customer added them in (most recent first).
        $products = Product::active()->whereIn('id', $ids)->get()->sortBy(fn ($p) => array_search($p->id, $ids))->values();

        return view('wishlist', compact('products'));
    }

    public function toggle(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);
        $product = Product::active()->findOrFail($request->product_id);
        $added = $this->wishlist->toggle($product->id);

        return response()->json([
            'added' => $added,
            'count' => $this->wishlist->count(),
            'message' => $added ? "{$product->name} added to your wishlist." : "{$product->name} removed from your wishlist.",
        ]);
    }
}
