@extends('emails.layout')

@section('body')
    @php
        $messages = [
            'confirmed' => 'Great news! Your order has been confirmed and is being prepared.',
            'processing' => 'Your order is being packed with care.',
            'shipped' => 'Your order is on its way! Our delivery partner will contact you soon.',
            'delivered' => 'Your order has been delivered. We hope you enjoy it — thank you for choosing us!',
            'cancelled' => 'Your order has been cancelled. If this was unexpected, please contact us.',
            'returned' => 'Your order has been marked as returned.',
        ];
    @endphp
    <h2 style="margin:0 0 6px;color:#222;">Order #{{ $order->order_number }} — {{ $order->status_label }}</h2>
    <p>Hi {{ $order->name }}, {{ $messages[$order->status] ?? 'there is an update on your order.' }}</p>
    @if($order->status === 'shipped' && $order->activeShipment)
        <p style="background:#f6f3ea;padding:12px 14px;border-radius:8px;">
            <strong>Courier:</strong> {{ $order->activeShipment->courier_label }}<br>
            <strong>Tracking ID:</strong> {{ $order->activeShipment->tracking_code ?: $order->activeShipment->consignment_id }}
            @if($order->activeShipment->tracking_url)<br><a href="{{ $order->activeShipment->tracking_url }}" style="color:{{ setting('primary_color', '#1f4d36') }};">Track parcel →</a>@endif
        </p>
    @endif
    @include('emails.partials.order-table')
    <p style="text-align:center;margin:20px 0 6px;">
        <a href="{{ route('track-order') }}" style="background:{{ setting('primary_color', '#1f4d36') }};color:#fff;padding:12px 26px;border-radius:6px;text-decoration:none;font-weight:600;display:inline-block;">View order status</a>
    </p>
@endsection
