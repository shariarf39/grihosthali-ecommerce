@extends('emails.layout')

@section('body')
    <h2 style="margin:0 0 6px;color:#222;">New order #{{ $order->order_number }}</h2>
    <p style="margin:0;"><strong>{{ $order->name }}</strong> · {{ $order->phone }}<br>{{ $order->address }} ({{ $order->shipping_area }})</p>
    @if($order->note)<p><strong>Note:</strong> {{ $order->note }}</p>@endif
    @include('emails.partials.order-table')
    <p style="text-align:center;"><a href="{{ route('admin.orders.show', $order) }}" style="background:#222;color:#fff;padding:11px 24px;border-radius:6px;text-decoration:none;display:inline-block;">Open in admin</a></p>
@endsection
