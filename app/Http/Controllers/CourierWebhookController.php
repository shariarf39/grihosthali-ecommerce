<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\Couriers\CourierManager;
use Illuminate\Http\Request;

/**
 * Receives delivery status pushes from couriers.
 * URL: /webhooks/courier/{slug}/{token} — the token is shown in Admin → Integrations → Couriers.
 */
class CourierWebhookController extends Controller
{
    public function __invoke(Request $request, string $slug, string $token, CourierManager $couriers)
    {
        abort_unless(array_key_exists($slug, CourierManager::LABELS), 404);
        abort_unless(hash_equals((string) setting('courier_webhook_token'), $token), 403);

        $consignment = $request->input('consignment_id') ?? $request->input('data.consignment_id');
        $orderNumber = $request->input('invoice') ?? $request->input('merchant_order_id') ?? $request->input('data.merchant_order_id');
        $status = $request->input('status') ?? $request->input('delivery_status') ?? $request->input('order_status')
            ?? $request->input('data.order_status') ?? ($request->input('event') ? str_replace('order.', '', $request->input('event')) : null);

        $shipment = Shipment::where('courier', $slug)
            ->when($consignment, fn ($q) => $q->where('consignment_id', (string) $consignment),
                fn ($q) => $q->whereIn('order_id', Order::where('order_number', $orderNumber)->select('id')))
            ->latest()->first();

        if ($shipment && $status) {
            $couriers->applyStatus($shipment, (string) $status);
        }

        // Pathao verifies webhook URLs by expecting HTTP 202 with this integration header.
        return response()->json(['status' => 'success', 'message' => 'Webhook received'], 202)
            ->header('X-Pathao-Merchant-Webhook-Integration-Secret', 'f3992ecc-59da-4cbe-a049-a13da2018d51');
    }
}
