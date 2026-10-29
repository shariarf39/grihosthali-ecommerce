@extends('emails.layout')

@section('body')
    <h2 style="margin:0 0 6px;color:#222;">Return request #{{ $return->return_number }}</h2>
    <p><strong>{{ $return->name }}</strong> ({{ $return->phone }}) wants a <strong>{{ $return->resolution }}</strong> for order #{{ $return->order->order_number }}.<br>Reason: {{ $return->reason }}</p>
    @if($return->customer_note)<p style="background:#f6f3ea;padding:10px 12px;border-radius:6px;">“{{ $return->customer_note }}”</p>@endif
    <ul>@foreach($return->items as $item)<li>{{ $item->product_name }} × {{ $item->quantity }} ({{ taka($item->total) }})</li>@endforeach</ul>
    <p style="text-align:center;"><a href="{{ route('admin.returns.show', $return) }}" style="background:#222;color:#fff;padding:11px 24px;border-radius:6px;text-decoration:none;display:inline-block;">Review return</a></p>
@endsection
