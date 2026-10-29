@extends('admin.layouts.app')

@section('title', 'Orders')

@section('content')
<div class="status-tabs">
    <a href="{{ route('admin.orders.index', request()->except('status', 'page')) }}" class="{{ request('status') ? '' : 'active' }}">All <span>{{ $counts->sum() }}</span></a>
    @foreach(\App\Models\Order::STATUSES as $key => $label)
        <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => $key])) }}" class="{{ request('status') === $key ? 'active' : '' }}">{{ $label }} <span>{{ $counts[$key] ?? 0 }}</span></a>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <form class="filters" method="GET">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input name="q" class="form-control" placeholder="Order #, name or phone" value="{{ request('q') }}">
            <input type="date" name="from" class="form-control" value="{{ request('from') }}" title="From">
            <input type="date" name="to" class="form-control" value="{{ request('to') }}" title="To">
            <button class="btn btn-light"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </form>
        @php $couriers = auth()->user()->can('courier.manage') ? app(\App\Services\Couriers\CourierManager::class)->available() : []; @endphp
        @if($couriers)
            <form method="POST" action="{{ route('admin.shipments.bulk-send') }}" id="bulk-form" class="bulk-bar"
                  data-confirm="Send the selected orders to the courier?">
                @csrf
                <span class="muted"><strong id="bulk-count">0</strong> selected</span>
                <select name="courier" class="form-control">
                    @foreach($couriers as $d)<option value="{{ $d->slug() }}" @selected(setting('courier_default') === $d->slug())>{{ $d->label() }}</option>@endforeach
                </select>
                <button class="btn" id="bulk-btn" disabled><i class="fa-solid fa-truck-fast"></i> Send to courier</button>
            </form>
        @endif
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr>@if($couriers)<th style="width:34px"><input type="checkbox" id="check-all" aria-label="Select all"></th>@endif<th>Order</th><th>Customer</th><th>Area</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Courier</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @forelse($orders as $o)
                    <tr>
                        @if($couriers)
                            <td>@unless($o->activeShipment || in_array($o->status, ['cancelled', 'returned', 'delivered']))<input type="checkbox" name="orders[]" value="{{ $o->id }}" form="bulk-form" class="row-check" aria-label="Select order">@endunless</td>
                        @endif
                        <td><a href="{{ route('admin.orders.show', $o) }}"><strong>#{{ $o->order_number }}</strong></a></td>
                        <td>{{ $o->name }}<div class="muted"><a href="tel:{{ $o->phone }}">{{ $o->phone }}</a></div></td>
                        <td>{{ $o->shipping_area }}</td>
                        <td>{{ $o->items_count }}</td>
                        <td><strong>{{ taka($o->total) }}</strong></td>
                        <td><span class="badge badge-{{ $o->payment_status === 'paid' ? 'success' : 'secondary' }}">{{ strtoupper($o->payment_method) }} · {{ ucfirst($o->payment_status) }}</span></td>
                        <td><span class="badge badge-{{ status_badge($o->status) }}">{{ $o->status_label }}</span></td>
                        <td>
                            @if($o->activeShipment)
                                <span class="courier-logo sm {{ $o->activeShipment->courier }}" title="{{ $o->activeShipment->courier_label }}">{{ mb_substr($o->activeShipment->courier_label, 0, 1) }}</span>
                                <span class="muted" style="font-size:12px">{{ $o->activeShipment->consignment_id }}</span>
                            @else<span class="muted">—</span>@endif
                        </td>
                        <td class="muted">{{ $o->created_at->format('d M Y') }}<br>{{ $o->created_at->format('h:i A') }}</td>
                        <td><div class="actions">
                            <a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-icon" title="View"><i class="fa-regular fa-eye"></i></a>
                            <a href="{{ route('admin.orders.invoice', $o) }}" target="_blank" class="btn btn-sm btn-light btn-icon" title="Invoice"><i class="fa-solid fa-print"></i></a>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="empty">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</div>
@endsection

@push('scripts')
<script>
(function () {
    const all = document.getElementById('check-all');
    if (!all) return;
    const rows = [...document.querySelectorAll('.row-check')];
    const update = () => {
        const n = rows.filter((r) => r.checked).length;
        document.getElementById('bulk-count').textContent = n;
        document.getElementById('bulk-btn').disabled = n === 0;
        all.checked = n > 0 && n === rows.length;
    };
    all.addEventListener('change', () => { rows.forEach((r) => { r.checked = all.checked; }); update(); });
    rows.forEach((r) => r.addEventListener('change', update));
})();
</script>
@endpush
