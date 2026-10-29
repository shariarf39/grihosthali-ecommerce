<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::active()->where('slug', $slug)->with(['category', 'brand', 'approvedReviews'])->firstOrFail();
        $product->increment('views');

        $related = Product::active()->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->inRandomOrder()->take(10)->get();

        $moreProducts = Product::active()->where('id', '!=', $product->id)->whereNotIn('id', $related->pluck('id'))
            ->orderByDesc('sold_count')->take(10)->get();

        $reviews = $product->approvedReviews;
        $avgRating = round($reviews->avg('rating') ?? 0, 1);

        return view('product', compact('product', 'related', 'moreProducts', 'reviews', 'avgRating'));
    }

    public function review(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:2000',
        ]);

        $product->reviews()->create($data + ['user_id' => auth()->id(), 'is_approved' => false]);

        return back()->with('success', 'Thank you! Your review will appear after approval.');
    }
}
