<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Page;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('page', compact('page'));
    }

    public function trackOrder(Request $request)
    {
        $order = null;
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'order_number' => 'required|string',
                'phone' => 'required|string',
            ]);
            $order = Order::where('order_number', strtoupper(trim($data['order_number'])))
                ->whereIn('phone', [trim($data['phone']), normalize_phone($data['phone'])])->with('items', 'returns')->first();
            if (! $order) {
                return back()->withInput()->with('error', 'No order found with this order number and phone.');
            }
            // Remember the verified lookup so a guest can request a return for this order.
            session()->push('verified_orders', $order->order_number);
        }

        return view('track-order', compact('order'));
    }

    public function contact()
    {
        return view('contact');
    }

    public function contactStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|max:3000',
        ]);
        ContactMessage::create($data);

        return back()->with('success', 'Thanks for reaching out! We will get back to you soon.');
    }

    public function subscribe(Request $request)
    {
        $request->validate(['email' => 'required|email|max:150']);
        Subscriber::firstOrCreate(['email' => strtolower($request->email)]);

        return back()->with('success', 'Subscribed successfully!');
    }
}
