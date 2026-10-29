@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
@include('partials.breadcrumb', ['items' => ['My Account' => route('account.index'), 'Order #' . $order->order_number => null]])

<div class="container" style="padding:28px 16px">
    <div class="account-card">
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <div><h3 style="margin:0">Order #{{ $order->order_number }}</h3><small>Placed on {{ $order->created_at->format('d M Y, h:i A') }}</small></div>
            <span class="badge badge-{{ status_badge($order->status) }}" style="font-size:14px;align-self:flex-start">{{ $order->status_label }}</span>
        </div>
        @include('partials.order-tracker')
        <div class="form-row">
            <div><h4>Shipping Address</h4><p>{{ $order->name }}<br>{{ $order->phone }}<br>{{ $order->address }}<br>{{ $order->shipping_area }}</p></div>
            <div><h4>Payment</h4><p>Cash on Delivery<br>Status: {{ ucfirst($order->payment_status) }}</p></div>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th style="text-align:right">Total</th></tr></thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr><td>{{ $item->product_name }}</td><td>{{ taka($item->price) }}</td><td>{{ $item->quantity }}</td><td style="text-align:right">{{ taka($item->total) }}</td></tr>
                    @endforeach
                    <tr><td colspan="3">Subtotal</td><td style="text-align:right">{{ taka($order->subtotal) }}</td></tr>
                    <tr><td colspan="3">Delivery</td><td style="text-align:right">{{ taka($order->shipping_charge) }}</td></tr>
                    @if($order->discount > 0)<tr><td colspan="3">Discount</td><td style="text-align:right">−{{ taka($order->discount) }}</td></tr>@endif
                    <tr><td colspan="3"><strong>Total</strong></td><td style="text-align:right"><strong>{{ taka($order->total) }}</strong></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
