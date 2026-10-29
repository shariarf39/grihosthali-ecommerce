@extends('layouts.app')

@section('title', 'Track Order')

@section('content')
@include('partials.breadcrumb', ['items' => ['Track Order' => null]])

<div class="container">
    <div class="auth-box" style="max-width:{{ $order ? '820px' : '480px' }}">
        <h2><i class="fa-solid fa-truck-fast" style="color:var(--primary)"></i> Track Your Order</h2>
        <p class="sub">Enter your order number and the phone number used at checkout.</p>

        <form method="POST" action="{{ route('track-order') }}">
            @csrf
            <div class="form-row">
                <div class="form-group"><label>Order Number</label><input name="order_number" class="form-control" value="{{ old('order_number', $order?->order_number) }}" placeholder="GH2610291234" required></div>
                <div class="form-group"><label>Phone Number</label><input name="phone" class="form-control" value="{{ old('phone', $order?->phone) }}" placeholder="01XXXXXXXXX" required></div>
            </div>
            <button class="btn btn-block">Track Order</button>
        </form>

        @if($order)
            <hr style="margin:28px 0;border:0;border-top:1px solid var(--border)">
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px">
                <div><strong style="color:var(--title)">Order #{{ $order->order_number }}</strong><br><small>Placed on {{ $order->created_at->format('d M Y, h:i A') }}</small></div>
                <div><span class="badge badge-{{ status_badge($order->status) }}" style="font-size:14px">{{ $order->status_label }}</span></div>
            </div>
            @include('partials.order-tracker')
            <table class="table">
                <thead><tr><th>Product</th><th>Qty</th><th style="text-align:right">Total</th></tr></thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td style="text-align:right">{{ taka($item->total) }}</td></tr>
                    @endforeach
                    <tr><td colspan="2"><strong>Total (incl. delivery)</strong></td><td style="text-align:right"><strong>{{ taka($order->total) }}</strong></td></tr>
                </tbody>
            </table>
            @include('partials.order-returns')
        @endif
    </div>
</div>
@endsection
