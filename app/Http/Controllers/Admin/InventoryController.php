<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%")))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->filter === 'low', fn ($q) => $q->lowStock()->where('stock', '>', 0))
            ->when($request->filter === 'out', fn ($q) => $q->where('stock', 0))
            ->when($request->filter === 'in', fn ($q) => $q->whereColumn('stock', '>', 'low_stock_alert'))
            ->orderBy($request->sort === 'name' ? 'name' : 'stock', $request->sort === 'stock_desc' ? 'desc' : 'asc')
            ->paginate(25)->withQueryString();

        $summary = [
            'skus' => Product::count(),
            'units' => (int) Product::sum('stock'),
            'cost_value' => (float) Product::sum(DB::raw('stock * COALESCE(cost_price, 0)')),
            'sale_value' => (float) Product::sum(DB::raw('stock * COALESCE(discount_price, price)')),
            'low' => Product::lowStock()->where('stock', '>', 0)->count(),
            'out' => Product::where('stock', 0)->count(),
        ];

        $categories = Category::orderBy('name')->get();

        return view('admin.inventory.index', compact('products', 'summary', 'categories'));
    }

    public function adjust(Request $request, Product $product)
    {
        $data = $request->validate([
            'action' => 'required|in:purchase,damage,adjustment_in,adjustment_out,audit',
            'quantity' => 'required|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:255',
        ]);
        $qty = (int) $data['quantity'];

        $movement = match ($data['action']) {
            'purchase' => Inventory::move($product, $qty, 'purchase', $data['reference'] ?? null, $data['note'] ?? null, $data['unit_cost'] ?? null),
            'adjustment_in' => Inventory::move($product, $qty, 'adjustment', $data['reference'] ?? null, $data['note'] ?? null),
            'adjustment_out' => Inventory::move($product, -$qty, 'adjustment', $data['reference'] ?? null, $data['note'] ?? null),
            'damage' => Inventory::move($product, -$qty, 'damage', $data['reference'] ?? null, $data['note'] ?? null),
            'audit' => Inventory::setCount($product, $qty, $data['note'] ?? 'Physical stock count'),
        };

        // Keep the latest purchase cost as the product's cost price.
        if ($data['action'] === 'purchase' && ! empty($data['unit_cost'])) {
            $product->update(['cost_price' => $data['unit_cost']]);
        }

        return back()->with('success', $movement
            ? "{$product->name}: stock {$movement->stock_before} → {$movement->stock_after}"
            : 'No change — stock already matches.');
    }

    public function movements(Request $request)
    {
        $movements = StockMovement::with(['product:id,name,image', 'user:id,name'])
            ->when($request->product, fn ($q, $p) => $q->where('product_id', $p))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()->paginate(30)->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('admin.inventory.movements', compact('movements', 'products'));
    }

    public function export()
    {
        $rows = Product::with('category')->orderBy('name')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['SKU', 'Product', 'Category', 'Stock', 'Low Stock Alert', 'Cost Price', 'Selling Price', 'Stock Value (Cost)', 'Stock Value (Sale)']);
            foreach ($rows as $p) {
                fputcsv($out, [$p->sku, $p->name, $p->category?->name, $p->stock, $p->low_stock_alert, $p->cost_price,
                    $p->sell_price, $p->stock * (float) $p->cost_price, $p->stock * $p->sell_price]);
            }
            fclose($out);
        }, 'inventory-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
