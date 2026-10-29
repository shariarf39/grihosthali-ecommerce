<?php

namespace App\Services;

use App\Models\CrmCustomer;
use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Shared return-request creation for the storefront and the admin panel. */
class Returns
{
    /** @return OrderReturn|string the created return, or an error message */
    public static function createFromRequest(Request $request, Order $order, string $createdBy): OrderReturn|string
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*' => 'nullable|integer|min:0',
            'reason' => ['required', Rule::in(OrderReturn::REASONS)],
            'resolution' => ['required', Rule::in(array_keys(OrderReturn::RESOLUTIONS))],
            'customer_note' => 'nullable|string|max:1000',
        ]);

        $order->loadMissing('items');
        $already = $order->returnedQuantities();
        $lines = [];
        foreach ($order->items as $item) {
            $qty = (int) ($data['items'][$item->id] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            $max = $item->quantity - ($already[$item->id] ?? 0);
            if ($qty > $max) {
                return "You can return at most {$max} × {$item->product_name}.";
            }
            $lines[] = [$item, $qty];
        }
        if (! $lines) {
            return 'Select at least one product and quantity to return.';
        }

        return DB::transaction(function () use ($order, $data, $lines, $createdBy) {
            $customer = $order->crm_customer_id ? $order->customer : CrmCustomer::syncFromOrder($order);

            $return = OrderReturn::create([
                'return_number' => OrderReturn::generateNumber(),
                'order_id' => $order->id,
                'crm_customer_id' => $customer?->id,
                'name' => $order->name,
                'phone' => $order->phone,
                'reason' => $data['reason'],
                'resolution' => $data['resolution'],
                'customer_note' => $data['customer_note'] ?? null,
                'created_by' => $createdBy,
            ]);

            $total = 0;
            foreach ($lines as [$item, $qty]) {
                $return->items()->create([
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $qty,
                    'price' => $item->price,
                    'total' => $item->price * $qty,
                ]);
                $total += $item->price * $qty;
            }
            $return->update(['refund_amount' => $data['resolution'] === 'refund' ? $total : 0]);

            $customer?->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'system',
                'message' => "Return #{$return->return_number} requested for order #{$order->order_number} ({$data['reason']})",
            ]);

            app(Notifier::class)->returnRequested($return);

            return $return;
        });
    }
}
