<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%")))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('is_active', $request->status))
            ->when($request->stock === 'low', fn ($q) => $q->lowStock())
            ->latest()->paginate(20)->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('admin.products.form', ['product' => new Product(['is_active' => true, 'stock' => 0, 'low_stock_alert' => 5])] + $this->options());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image'] = $request->hasFile('image') ? upload_file($request->file('image'), 'products') : null;
        $data['gallery'] = $this->uploadGallery($request, []);

        $stock = $data['stock'];
        $product = Product::create(['stock' => 0] + $data);
        Inventory::move($product, $stock, 'opening', null, 'Initial stock', $data['cost_price'] ?? null);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', compact('product') + $this->options());
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);
        if ($request->hasFile('image')) {
            $data['image'] = upload_file($request->file('image'), 'products', $product->image);
        }

        $gallery = $product->gallery ?? [];
        foreach ((array) $request->input('remove_gallery', []) as $path) {
            if (($i = array_search($path, $gallery, true)) !== false) {
                delete_upload($path);
                unset($gallery[$i]);
            }
        }
        $data['gallery'] = $this->uploadGallery($request, array_values($gallery));

        // Stock edits from the product form are logged as adjustments, never written silently.
        $stockChange = $data['stock'] - $product->stock;
        unset($data['stock']);
        $product->update($data);
        Inventory::move($product, $stockChange, 'adjustment', null, 'Edited from product form');

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        delete_upload($product->image);
        foreach ($product->gallery ?? [] as $img) {
            delete_upload($img);
        }
        $product->delete();

        return back()->with('success', 'Product deleted.');
    }

    public function toggle(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', 'Status updated.');
    }

    private function options(): array
    {
        return [
            'categories' => Category::with('parent')->orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'flags' => ['Best Selling', 'New Arrival', 'Hot Deal', 'Limited Stock', 'Organic'],
        ];
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product?->id)],
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'sku' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'low_stock_alert' => 'required|integer|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'flag' => 'nullable|string|max:50',
            'short_description' => 'nullable|string|max:1000',
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'image' => ($product ? 'nullable' : 'required') . '|image|max:4096',
            'gallery.*' => 'nullable|image|max:4096',
        ], ['discount_price.lt' => 'Discount price must be less than the regular price.']);

        $data['slug'] = Str::slug(($data['slug'] ?? null) ?: $data['name']);
        if (Product::where('slug', $data['slug'])->where('id', '!=', $product?->id)->exists()) {
            $data['slug'] .= '-' . Str::lower(Str::random(4));
        }
        $data['is_active'] = $request->boolean('is_active');
        $data['is_top_selling'] = $request->boolean('is_top_selling');
        unset($data['image'], $data['gallery']);

        return $data;
    }

    private function uploadGallery(Request $request, array $gallery): array
    {
        foreach ((array) $request->file('gallery', []) as $file) {
            $gallery[] = upload_file($file, 'products');
        }

        return $gallery;
    }
}
