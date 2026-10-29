<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $valid = fn () => Order::whereNotIn('status', ['cancelled', 'returned']);

        $stats = [
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_sales' => $valid()->whereDate('created_at', today())->sum('total'),
            'month_sales' => $valid()->whereBetween('created_at', [now()->startOfMonth(), now()])->sum('total'),
            'total_sales' => $valid()->where('status', 'delivered')->sum('total'),
            'total_orders' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'products' => Product::count(),
            'customers' => User::where('is_admin', false)->count(),
            'low_stock' => Product::where('stock', '<=', 5)->count(),
        ];

        $statusCounts = Order::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        // Sales for the last 14 days, zero-filled.
        $daily = $valid()->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as orders'))
            ->groupBy('d')->get()->keyBy('d');
        $chart = collect(range(13, 0))->map(function ($i) use ($daily) {
            $date = now()->subDays($i)->toDateString();

            return [
                'label' => Carbon::parse($date)->format('d M'),
                'total' => (float) ($daily[$date]->total ?? 0),
                'orders' => (int) ($daily[$date]->orders ?? 0),
            ];
        });

        $recentOrders = Order::latest()->take(8)->get();
        $topProducts = Product::orderByDesc('sold_count')->take(5)->get();
        $lowStock = Product::where('stock', '<=', 5)->orderBy('stock')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'statusCounts', 'chart', 'recentOrders', 'topProducts', 'lowStock'));
    }

    public function reports(Request $request)
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = ($request->date('to') ?? now())->copy()->endOfDay();

        $orders = Order::whereBetween('created_at', [$from->copy()->startOfDay(), $to])->whereNotIn('status', ['cancelled', 'returned']);

        $summary = [
            'orders' => (clone $orders)->count(),
            'subtotal' => (clone $orders)->sum('subtotal'),
            'shipping' => (clone $orders)->sum('shipping_charge'),
            'discount' => (clone $orders)->sum('discount'),
            'total' => (clone $orders)->sum('total'),
        ];

        $products = OrderItem::whereIn('order_id', (clone $orders)->select('id'))
            ->select('product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(total) as amount'))
            ->groupBy('product_name')->orderByDesc('qty')->get();

        return view('admin.reports', compact('summary', 'products', 'from', 'to'));
    }
}
