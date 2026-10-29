@extends('admin.layouts.app')

@section('title', 'Return #' . $return->return_number)

@section('content')
<div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap">
    <a href="{{ route('admin.returns.index') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i> All Returns</a>
    <a href="{{ route('admin.orders.show', $return->order) }}" class="btn btn-light"><i class="fa-solid fa-receipt"></i> Order #{{ $return->order->order_number }}</a>
    @if($return->customer)<a href="{{ route('admin.crm.show', $return->customer) }}" class="btn btn-light"><i class="fa-regular fa-user"></i> Customer Profile</a>@endif
</div>

{{-- Progress --}}
@php
    $flow = $return->resolution === 'replacement' ? ['requested', 'approved', 'received', 'replaced'] : ['requested', 'approved', 'received', 'refunded'];
    $pos = array_search($return->status, $flow);
@endphp
<div class="card">
    <div class="card-body">
        @if($return->status === 'rejected')
            <div class="alert alert-danger" style="margin:0"><i class="fa-solid fa-ban"></i> This return request was rejected.</div>
        @else
            <div class="progress-steps">
                @foreach($flow as $i => $step)
                    <div class="ps {{ $pos !== false && $i <= $pos ? 'done' : '' }}"><span>{{ $i + 1 }}</span>{{ \App\Models\OrderReturn::STATUSES[$step][0] }}</div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<form method="POST" action="{{ route('admin.returns.update', $return) }}">
    @csrf @method('PUT')
    <div class="grid-2">
        <div>
            <div class="card">
                <div class="card-header"><h3>Returned Items</h3><span class="badge badge-{{ $return->status_badge }}">{{ $return->status_label }}</span></div>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th>Condition on receipt</th></tr></thead>
                        <tbody>
                            @foreach($return->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ taka($item->price) }}</td>
                                    <td>{{ taka($item->total) }}</td>
                                    <td>
                                        <select name="condition[{{ $item->id }}]" class="form-control" @disabled($return->restocked)>
                                            <option value="resellable" @selected($item->condition === 'resellable')>Resellable → back to stock</option>
                                            <option value="damaged" @selected($item->condition === 'damaged')>Damaged → not restocked</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                            <tr><td colspan="3" style="text-align:right"><strong>Items value</strong></td><td colspan="2"><strong>{{ taka($return->items_total) }}</strong></td></tr>
                        </tbody>
                    </table>
                </div>
                @if($return->restocked)
                    <div class="card-body muted"><i class="fa-solid fa-boxes-stacked"></i> Resellable items were added back to stock on {{ $return->received_at?->format('d M Y') }}.</div>
                @else
                    <div class="card-body muted"><i class="fa-solid fa-circle-info"></i> Resellable items go back into stock automatically when the status becomes “Product Received”.</div>
                @endif
            </div>
            <div class="card">
                <div class="card-header"><h3>Request Details</h3></div>
                <div class="card-body">
                    <ul class="info-list">
                        <li><span>Customer</span><span>{{ $return->name }} · {{ $return->phone }}</span></li>
                        <li><span>Reason</span><span>{{ $return->reason }}</span></li>
                        <li><span>Wants</span><span>{{ \App\Models\OrderReturn::RESOLUTIONS[$return->resolution] ?? $return->resolution }}</span></li>
                        <li><span>Requested</span><span>{{ $return->created_at->format('d M Y, h:i A') }} (by {{ $return->created_by }})</span></li>
                        <li><span>Order delivered</span><span>{{ $return->order->delivered_at?->format('d M Y') ?? '—' }}</span></li>
                    </ul>
                    @if($return->customer_note)<p style="margin-top:14px"><strong>Customer note:</strong> {{ $return->customer_note }}</p>@endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Process Return</h3></div>
            <div class="card-body">
                <div class="form-group"><label>Status</label>
                    <select name="status" class="form-control">
                        @foreach(\App\Models\OrderReturn::STATUSES as $k => [$label])<option value="{{ $k }}" @selected($return->status === $k)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label>Refund Amount (৳)</label><input type="number" step="0.01" name="refund_amount" class="form-control" value="{{ old('refund_amount', $return->refund_amount) }}"><div class="hint">Order total: {{ taka($return->order->total) }}</div></div>
                <div class="form-row">
                    <div class="form-group"><label>Refund Method</label>
                        <select name="refund_method" class="form-control">
                            <option value="">—</option>
                            @foreach(\App\Models\OrderReturn::REFUND_METHODS as $k => $l)<option value="{{ $k }}" @selected($return->refund_method === $k)>{{ $l }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Transaction / Ref</label><input name="refund_reference" class="form-control" value="{{ old('refund_reference', $return->refund_reference) }}" placeholder="TrxID"></div>
                </div>
                <div class="form-group"><label>Admin Note</label><textarea name="admin_note" rows="3" class="form-control">{{ old('admin_note', $return->admin_note) }}</textarea></div>
                <button class="btn" style="width:100%;justify-content:center"><i class="fa-solid fa-floppy-disk"></i> Update Return</button>
                @if($return->refunded_at)<p class="muted" style="margin-top:12px"><i class="fa-solid fa-check"></i> Refunded {{ taka($return->refund_amount) }} via {{ \App\Models\OrderReturn::REFUND_METHODS[$return->refund_method] ?? $return->refund_method }} on {{ $return->refunded_at->format('d M Y') }}</p>@endif
            </div>
        </div>
    </div>
</form>
@endsection
