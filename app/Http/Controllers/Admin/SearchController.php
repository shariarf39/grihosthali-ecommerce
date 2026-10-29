<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmCustomer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shipment;
use Illuminate\Http\Request;

/** Global admin search across orders, customers, products and consignments. */
class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->q);
        $user = $request->user();
        $results = ['orders' => collect(), 'customers' => collect(), 'products' => collect(), 'shipments' => collect()];

        if (mb_strlen($q) >= 2) {
            $phone = normalize_phone($q);
            if ($user->can('orders.view')) {
                $results['orders'] = Order::where('order_number', 'like', "%$q%")->orWhere('name', 'like', "%$q%")
                    ->when(strlen($phone) >= 4, fn ($w) => $w->orWhere('phone', 'like', "%$phone%"))->latest()->take(10)->get();
                $results['shipments'] = Shipment::with('order:id,order_number')->where('consignment_id', 'like', "%$q%")
                    ->orWhere('tracking_code', 'like', "%$q%")->latest()->take(5)->get();
            }
            if ($user->can('customers.manage')) {
                $results['customers'] = CrmCustomer::where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%")
                    ->when(strlen($phone) >= 4, fn ($w) => $w->orWhere('phone', 'like', "%$phone%"))->take(10)->get();
            }
            if ($user->can('products.manage')) {
                $results['products'] = Product::where('name', 'like', "%$q%")->orWhere('sku', 'like', "%$q%")->take(10)->get();
            }
        }

        return view('admin.search', compact('q', 'results'));
    }
}
