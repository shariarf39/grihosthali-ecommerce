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
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Order</th><th>Customer</th><th>Area</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $o) }}"><strong>#{{ $o->order_number }}</strong></a></td>
                        <td>{{ $o->name }}<div class="muted"><a href="tel:{{ $o->phone }}">{{ $o->phone }}</a></div></td>
                        <td>{{ $o->shipping_area }}</td>
                        <td>{{ $o->items_count }}</td>
                        <td><strong>{{ taka($o->total) }}</strong></td>
                        <td><span class="badge badge-{{ $o->payment_status === 'paid' ? 'success' : 'secondary' }}">{{ strtoupper($o->payment_method) }} · {{ ucfirst($o->payment_status) }}</span></td>
                        <td><span class="badge badge-{{ status_badge($o->status) }}">{{ $o->status_label }}</span></td>
                        <td class="muted">{{ $o->created_at->format('d M Y') }}<br>{{ $o->created_at->format('h:i A') }}</td>
                        <td><div class="actions">
                            <a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-icon" title="View"><i class="fa-regular fa-eye"></i></a>
                            <a href="{{ route('admin.orders.invoice', $o) }}" target="_blank" class="btn btn-sm btn-light btn-icon" title="Invoice"><i class="fa-solid fa-print"></i></a>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</div>
@endsection
