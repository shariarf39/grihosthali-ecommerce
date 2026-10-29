@extends('emails.layout')

@section('body')
    <h2 style="margin:0 0 6px;color:#222;">Return #{{ $return->return_number }}: {{ $return->status_label }}</h2>
    <p>Hi {{ $return->name }}, here is the latest update on your return for order #{{ $return->order->order_number }}.</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:14px 0;font-size:14px;">
        @foreach($return->items as $item)
            <tr><td style="padding:8px 10px;border-bottom:1px solid #eee;">{{ $item->product_name }}</td><td align="right" style="padding:8px 10px;border-bottom:1px solid #eee;">× {{ $item->quantity }}</td></tr>
        @endforeach
    </table>
    @if($return->status === 'refunded')
        <p style="background:#e8f6ee;padding:12px 14px;border-radius:8px;color:#1d6b3e;">
            We have refunded <strong>{{ taka($return->refund_amount) }}</strong> via {{ \App\Models\OrderReturn::REFUND_METHODS[$return->refund_method] ?? $return->refund_method }}@if($return->refund_reference) (Ref: {{ $return->refund_reference }})@endif.
        </p>
    @elseif($return->status === 'rejected')
        <p>Unfortunately we could not accept this return. {{ $return->admin_note }}</p>
    @elseif($return->status === 'approved')
        <p>Your return is approved. Our delivery partner will collect the product from you shortly.</p>
    @endif
@endsection
