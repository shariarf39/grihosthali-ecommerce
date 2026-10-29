<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmActivity;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Services\Inventory;
use App\Services\Returns;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = OrderReturn::with('order:id,order_number')->withSum('items', 'total')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('return_number', 'like', "%$s%")
                ->orWhere('phone', 'like', "%$s%")->orWhere('name', 'like', "%$s%")
                ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%$s%"))))
            ->latest()->paginate(20)->withQueryString();

        $counts = OrderReturn::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $refunded = (float) OrderReturn::where('status', 'refunded')->sum('refund_amount');

        return view('admin.returns.index', compact('returns', 'counts', 'refunded'));
    }

    public function create(Order $order)
    {
        $order->load('items');
        $returned = $order->returnedQuantities();

        return view('admin.returns.create', compact('order', 'returned'));
    }

    public function store(Request $request, Order $order)
    {
        $return = Returns::createFromRequest($request, $order, 'admin');
        if (is_string($return)) {
            return back()->withInput()->with('error', $return);
        }

        return redirect()->route('admin.returns.show', $return)->with('success', "Return #{$return->return_number} created.");
    }

    public function show(OrderReturn $return)
    {
        $return->load('items.product', 'order.items', 'customer');

        return view('admin.returns.show', compact('return'));
    }

    public function update(Request $request, OrderReturn $return)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(OrderReturn::STATUSES))],
            'refund_amount' => 'nullable|numeric|min:0|max:' . $return->order->total,
            'refund_method' => ['nullable', Rule::in(array_keys(OrderReturn::REFUND_METHODS))],
            'refund_reference' => 'nullable|string|max:100',
            'admin_note' => 'nullable|string|max:2000',
            'condition' => 'nullable|array',
            'condition.*' => 'in:resellable,damaged',
        ]);

        if ($data['status'] === 'refunded' && empty($data['refund_method'])) {
            return back()->with('error', 'Select the refund method before marking as refunded.');
        }

        DB::transaction(function () use ($return, $data) {
            foreach ($data['condition'] ?? [] as $itemId => $condition) {
                $return->items()->whereKey($itemId)->update(['condition' => $condition]);
            }
            $return->load('items');

            // Put resellable items back into stock once the goods are physically received.
            $receivedNow = in_array($data['status'], ['received', 'refunded', 'replaced']) && ! $return->restocked;
            if ($receivedNow) {
                foreach ($return->items as $item) {
                    if ($item->product_id && $item->condition === 'resellable') {
                        Inventory::move($item->product_id, $item->quantity, 'return', $return->return_number, 'Returned by customer');
                    }
                }
                $return->restocked = true;
                $return->received_at = $return->received_at ?? now();
            }

            // A replacement ships new goods, so stock goes out again.
            if ($data['status'] === 'replaced' && $return->status !== 'replaced') {
                foreach ($return->items as $item) {
                    if ($item->product_id) {
                        Inventory::move($item->product_id, -$item->quantity, 'sale', $return->return_number, 'Replacement sent');
                    }
                }
            }

            if ($data['status'] === 'refunded') {
                $return->refunded_at = $return->refunded_at ?? now();
                $order = $return->order;
                $totalRefunded = $order->returns()->where('status', 'refunded')->whereKeyNot($return->id)->sum('refund_amount') + ($data['refund_amount'] ?? 0);
                $order->update(['payment_status' => $totalRefunded >= $order->total ? 'refunded' : 'partially_refunded']);
            }

            // When every unit of the order has come back, mark the order itself as returned.
            if (in_array($data['status'], ['received', 'refunded'])) {
                $order = $return->order->load('items');
                if (array_sum($order->returnedQuantities()) >= $order->items->sum('quantity')) {
                    $order->update(['status' => 'returned']);
                }
            }

            if ($return->status !== $data['status'] && $return->crm_customer_id) {
                CrmActivity::create([
                    'crm_customer_id' => $return->crm_customer_id,
                    'user_id' => auth()->id(),
                    'type' => 'system',
                    'message' => "Return #{$return->return_number}: " . OrderReturn::STATUSES[$return->status][0] . ' → ' . OrderReturn::STATUSES[$data['status']][0],
                ]);
            }

            unset($data['condition']);
            $statusChanged = $return->status !== $data['status'];
            $return->fill($data)->save();
            if ($statusChanged) {
                app(\App\Services\Notifier::class)->returnStatusChanged($return);
            }
        });

        return back()->with('success', 'Return updated.');
    }
}
