<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $products = $this->filtered($request, Product::active())->paginate(20)->withQueryString();
        $title = $request->filled('q') ? 'Search results for "' . $request->q . '"' : 'All Products';

        return $this->listing($request, $products, $title);
    }

    public function category(Request $request, string $slug)
    {
        $category = Category::active()->where('slug', $slug)->firstOrFail();
        $ids = $category->children()->pluck('id')->push($category->id);
        $products = $this->filtered($request, Product::active()->whereIn('category_id', $ids))->paginate(20)->withQueryString();

        return $this->listing($request, $products, $category->name, $category);
    }

    public function brand(Request $request, string $slug)
    {
        $brand = Brand::active()->where('slug', $slug)->firstOrFail();
        $products = $this->filtered($request, $brand->products()->active())->paginate(20)->withQueryString();

        return $this->listing($request, $products, $brand->name);
    }

    /** Live search dropdown in the header. */
    public function suggest(Request $request)
    {
        $q = trim((string) $request->q);
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        return Product::active()->where('name', 'like', "%{$q}%")->take(8)->get()->map(fn ($p) => [
            'name' => $p->name,
            'url' => route('product.show', $p->slug),
            'image' => $p->image_url,
            'price' => taka($p->sell_price),
        ]);
    }

    private function filtered(Request $request, $query)
    {
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('short_description', 'like', "%{$q}%"));
        }
        if ($request->filled('min')) {
            $query->whereRaw('COALESCE(discount_price, price) >= ?', [(float) $request->min]);
        }
        if ($request->filled('max')) {
            $query->whereRaw('COALESCE(discount_price, price) <= ?', [(float) $request->max]);
        }

        return match ($request->sort) {
            'price_low' => $query->orderByRaw('COALESCE(discount_price, price) asc'),
            'price_high' => $query->orderByRaw('COALESCE(discount_price, price) desc'),
            'popular' => $query->orderByDesc('sold_count'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };
    }

    private function listing(Request $request, $products, string $title, ?Category $category = null)
    {
        $categories = Category::active()->whereNull('parent_id')->withCount(['products' => fn ($q) => $q->active()])->orderBy('sort_order')->get();

        return view('shop', compact('products', 'title', 'category', 'categories'));
    }
}
