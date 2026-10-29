@extends('layouts.app')

@section('title', 'Order Placed')

@section('content')
<div class="container">
    <div class="success-box">
        <div class="success-icon"><i class="fa-solid fa-check"></i></div>
        <h2>Thank you for your order!</h2>
        <p class="bn" style="font-size:16px">আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে। আমাদের প্রতিনিধি শীঘ্রই আপনার সাথে যোগাযোগ করবেন।</p>

        <div class="order-meta">
            <div><small>Order Number</small><strong>{{ $order->order_number }}</strong></div>
            <div><small>Total Amount</small><strong>{{ taka($order->total) }}</strong></div>
            <div><small>Payment</small><strong>Cash on Delivery</strong></div>
        </div>

        <div class="table-wrap" style="text-align:left">
            <table class="table">
                <thead><tr><th>Product</th><th>Qty</th><th style="text-align:right">Total</th></tr></thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td style="text-align:right">{{ taka($item->total) }}</td></tr>
                    @endforeach
                    <tr><td colspan="2">Subtotal</td><td style="text-align:right">{{ taka($order->subtotal) }}</td></tr>
                    <tr><td colspan="2">Delivery ({{ $order->shipping_area }})</td><td style="text-align:right">{{ taka($order->shipping_charge) }}</td></tr>
                    @if($order->discount > 0)
                        <tr><td colspan="2">Discount ({{ $order->coupon_code }})</td><td style="text-align:right">−{{ taka($order->discount) }}</td></tr>
                    @endif
                    <tr><td colspan="2"><strong>Total</strong></td><td style="text-align:right"><strong style="color:var(--primary)">{{ taka($order->total) }}</strong></td></tr>
                </tbody>
            </table>
        </div>

        <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;flex-wrap:wrap">
            <a href="{{ route('shop') }}" class="btn btn-outline"><i class="fa-solid fa-store"></i> Continue Shopping</a>
            <a href="{{ route('track-order') }}" class="btn"><i class="fa-solid fa-truck-fast"></i> Track Order</a>
        </div>
    </div>
</div>
@endsection
