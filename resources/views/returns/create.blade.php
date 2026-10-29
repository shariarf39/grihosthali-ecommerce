@extends('layouts.app')

@section('title', 'Return / Refund Request')

@section('content')
@include('partials.breadcrumb', ['items' => ['Track Order' => route('track-order'), 'Return Request' => null]])

<div class="container">
    <div class="return-page">
        <div class="checkout-card">
            <h3><i class="fa-solid fa-rotate-left" style="color:var(--primary)"></i> Return / Refund Request</h3>
            <p class="bn" style="margin-top:-6px">{{ setting('return_policy_note') }}</p>

            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

            <form method="POST" action="{{ route('returns.store', $order->order_number) }}">
                @csrf
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Product</th><th>Price</th><th>Bought</th><th style="width:120px">Return Qty</th></tr></thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @php $left = $item->quantity - ($returned[$item->id] ?? 0); @endphp
                                <tr>
                                    <td>
                                        <div style="display:flex;gap:10px;align-items:center">
                                            <img src="{{ upload_url($item->image, 'images/placeholder.svg') }}" alt="" style="width:48px;height:48px;border-radius:6px;object-fit:cover">
                                            <span style="color:var(--title);font-weight:500">{{ $item->product_name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ taka($item->price) }}</td>
                                    <td>{{ $item->quantity }}@if($returned[$item->id] ?? 0)<div style="font-size:12px;color:var(--muted)">{{ $returned[$item->id] }} already requested</div>@endif</td>
                                    <td><input type="number" name="items[{{ $item->id }}]" min="0" max="{{ $left }}" value="{{ old('items.' . $item->id, 0) }}" class="form-control" @disabled($left <= 0)></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="form-row" style="margin-top:18px">
                    <div class="form-group"><label>Reason <span class="req">*</span></label>
                        <select name="reason" class="form-control" required>
                            @foreach(\App\Models\OrderReturn::REASONS as $r)<option @selected(old('reason') === $r)>{{ $r }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>I would like <span class="req">*</span></label>
                        <div class="radio-cards">
                            @foreach(\App\Models\OrderReturn::RESOLUTIONS as $k => $l)
                                <div class="radio-card"><input type="radio" name="resolution" id="res-{{ $k }}" value="{{ $k }}" @checked(old('resolution', 'refund') === $k)><label for="res-{{ $k }}">{{ $l }}</label></div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="form-group"><label>Details (optional)</label><textarea name="customer_note" rows="3" class="form-control" placeholder="Describe the problem. You can also send photos to our WhatsApp.">{{ old('customer_note') }}</textarea></div>
                <button class="btn btn-lg"><i class="fa-solid fa-paper-plane"></i> Submit Return Request</button>
            </form>
        </div>

        <div>
            <div class="checkout-card">
                <h3>Order #{{ $order->order_number }}</h3>
                <ul class="mini-info">
                    <li><span>Delivered</span><strong>{{ ($order->delivered_at ?? $order->updated_at)->format('d M Y') }}</strong></li>
                    <li><span>Total</span><strong>{{ taka($order->total) }}</strong></li>
                    <li><span>Return window</span><strong>{{ setting('return_days', 7) }} days</strong></li>
                </ul>
            </div>
            @if($order->returns->count())
                <div class="checkout-card" style="margin-top:18px">
                    <h3>Your return requests</h3>
                    @foreach($order->returns as $r)
                        <div class="return-status">
                            <div><strong>#{{ $r->return_number }}</strong><small>{{ $r->created_at->format('d M Y') }} · {{ $r->items->sum('quantity') }} item(s)</small></div>
                            <span class="badge badge-{{ $r->status_badge }}">{{ $r->status_label }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
            @if(setting('whatsapp'))
                <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}?text={{ urlencode('Return request for order #' . $order->order_number) }}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-block" style="margin-top:18px"><i class="fa-brands fa-whatsapp"></i> Send photos on WhatsApp</a>
            @endif
        </div>
    </div>
</div>
@endsection
