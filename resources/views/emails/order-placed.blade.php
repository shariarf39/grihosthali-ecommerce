@extends('emails.layout')

@section('body')
    <h2 style="margin:0 0 6px;color:#222;">Thank you for your order, {{ $order->name }}!</h2>
    <p style="margin:0 0 4px;">{{ setting('order_success_message') }}</p>
    <p style="margin:14px 0 0;"><strong>Order #{{ $order->order_number }}</strong> · {{ $order->created_at->format('d M Y, h:i A') }}</p>
    @include('emails.partials.order-table')
    <p style="margin:0;"><strong>Delivery to:</strong> {{ $order->address }}<br><strong>Payment:</strong> Cash on Delivery</p>
    <p style="text-align:center;margin:26px 0 6px;">
        <a href="{{ route('track-order') }}" style="background:{{ setting('primary_color', '#1f4d36') }};color:#fff;padding:12px 26px;border-radius:6px;text-decoration:none;font-weight:600;display:inline-block;">Track your order</a>
    </p>
@endsection
