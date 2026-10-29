@extends('admin.layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="btn btn-gold"><i class="fa-solid fa-print"></i> Print Invoice</a>
    <a href="tel:{{ $order->phone }}" class="btn btn-light"><i class="fa-solid fa-phone"></i> Call Customer</a>
    @if(in_array($order->status, ['delivered', 'returned']))
        <a href="{{ route('admin.returns.create', $order) }}" class="btn btn-light"><i class="fa-solid fa-rotate-left"></i> Create Return</a>
    @endif
</div>

<div class="grid-2">
    <div>
        <div class="card">
            <div class="card-header"><h3>Items</h3><span class="badge badge-{{ status_badge($order->status) }}">{{ $order->status_label }}</span></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th style="text-align:right">Total</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div style="display:flex;gap:10px;align-items:center">
                                        <img src="{{ upload_url($item->image, 'images/placeholder.svg') }}" class="thumb" alt="">
                                        <div>{{ $item->product_name }}@if($item->product)<div class="muted">Stock now: {{ $item->product->stock }}</div>@endif</div>
                                    </div>
                                </td>
                                <td>{{ taka($item->price) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td style="text-align:right">{{ taka($item->total) }}</td>
                            </tr>
                        @endforeach
                        <tr><td colspan="3" style="text-align:right">Subtotal</td><td style="text-align:right">{{ taka($order->subtotal) }}</td></tr>
                        <tr><td colspan="3" style="text-align:right">Delivery ({{ $order->shipping_area }})</td><td style="text-align:right">{{ taka($order->shipping_charge) }}</td></tr>
                        @if($order->discount > 0)
                            <tr><td colspan="3" style="text-align:right">Discount ({{ $order->coupon_code }})</td><td style="text-align:right">−{{ taka($order->discount) }}</td></tr>
                        @endif
                        <tr><td colspan="3" style="text-align:right"><strong>Grand Total</strong></td><td style="text-align:right"><strong>{{ taka($order->total) }}</strong></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        @if($order->note)
            <div class="card"><div class="card-header"><h3>Customer Note</h3></div><div class="card-body">{{ $order->note }}</div></div>
        @endif
        @if($order->returns->count())
            <div class="card">
                <div class="card-header"><h3>Returns for this order</h3></div>
                <div class="table-wrap"><table class="table">
                    @foreach($order->returns as $r)
                        <tr><td><a href="{{ route('admin.returns.show', $r) }}">#{{ $r->return_number }}</a></td><td>{{ $r->reason }}</td>
                            <td><span class="badge badge-{{ $r->status_badge }}">{{ $r->status_label }}</span></td><td>{{ taka($r->refund_amount) }}</td></tr>
                    @endforeach
                </table></div>
            </div>
        @endif
    </div>

    <div>
        <div class="card">
            <div class="card-header"><h3>Update Order</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                    @csrf @method('PUT')
                    <div class="form-group"><label>Order Status</label>
                        <select name="status" class="form-control">
                            @foreach(\App\Models\Order::STATUSES as $k => $l)<option value="{{ $k }}" @selected($order->status === $k)>{{ $l }}</option>@endforeach
                        </select>
                        <div class="hint">Cancelling/returning puts items back into stock.</div>
                    </div>
                    <div class="form-group"><label>Payment Status</label>
                        <select name="payment_status" class="form-control">
                            @foreach(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'partially_refunded' => 'Partially Refunded', 'refunded' => 'Refunded'] as $k => $l)<option value="{{ $k }}" @selected($order->payment_status === $k)>{{ $l }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Admin Note (internal)</label><textarea name="admin_note" rows="3" class="form-control">{{ $order->admin_note }}</textarea></div>
                    <button class="btn" style="width:100%;justify-content:center"><i class="fa-solid fa-floppy-disk"></i> Update</button>
                </form>
            </div>
        </div>
        @if($customer)
            <div class="card insight-card">
                <div class="card-header"><h3><i class="fa-solid fa-shield-halved" style="color:var(--gold)"></i> Customer Insight</h3>
                    <span class="badge badge-{{ \App\Models\CrmCustomer::segmentBadge($customer->segment) }}">{{ $customer->segment }}</span></div>
                <div class="card-body">
                    @if($customer->is_blocked)<div class="alert alert-danger">Blocked customer — {{ $customer->block_reason }}</div>@endif
                    <div class="insight-grid">
                        <div><strong>{{ $customer->orders_count }}</strong><small>Orders</small></div>
                        <div><strong style="color:var(--success)">{{ $customer->delivered_count }}</strong><small>Delivered</small></div>
                        <div><strong style="color:var(--danger)">{{ $customer->failed_count }}</strong><small>Cancel/Return</small></div>
                    </div>
                    @if($customer->success_rate !== null)
                        <div class="meter"><span style="width:{{ $customer->success_rate }}%;background:{{ $customer->success_rate >= 80 ? 'var(--success)' : ($customer->success_rate >= 50 ? 'var(--warning)' : 'var(--danger)') }}"></span></div>
                        <small class="muted">{{ $customer->success_rate }}% delivery success</small>
                    @else
                        <small class="muted">First-time customer — consider a confirmation call.</small>
                    @endif
                    <a href="{{ route('admin.crm.show', $customer) }}" class="btn btn-light" style="width:100%;justify-content:center;margin-top:12px"><i class="fa-regular fa-address-card"></i> Open CRM Profile</a>
                </div>
            </div>
        @endif
        <div class="card">
            <div class="card-header"><h3>Customer</h3></div>
            <div class="card-body">
                <ul class="info-list">
                    <li><span>Name</span><span>{{ $order->name }}</span></li>
                    <li><span>Phone</span><span><a href="tel:{{ $order->phone }}">{{ $order->phone }}</a></span></li>
                    @if($order->email)<li><span>Email</span><span>{{ $order->email }}</span></li>@endif
                    <li><span>Address</span><span>{{ $order->address }}</span></li>
                    <li><span>Area</span><span>{{ $order->shipping_area }}</span></li>
                    <li><span>Account</span><span>@if($order->user)<a href="{{ route('admin.customers.show', $order->user) }}">{{ $order->user->name }}</a>@else Guest @endif</span></li>
                    <li><span>Placed</span><span>{{ $order->created_at->format('d M Y, h:i A') }}</span></li>
                </ul>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" data-confirm="Permanently delete this order?">
            @csrf @method('DELETE')
            <button class="btn btn-danger" style="width:100%;justify-content:center"><i class="fa-regular fa-trash-can"></i> Delete Order</button>
        </form>
    </div>
</div>
@endsection
