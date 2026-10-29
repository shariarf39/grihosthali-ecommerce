@extends('admin.layouts.app')

@section('title', 'Returns & Refunds')

@section('content')
<div class="stats">
    <div class="stat"><div class="icon gold"><i class="fa-solid fa-inbox"></i></div><div><small>New Requests</small><strong>{{ $counts['requested'] ?? 0 }}</strong></div></div>
    <div class="stat"><div class="icon blue"><i class="fa-solid fa-truck-ramp-box"></i></div><div><small>Awaiting Pickup / Received</small><strong>{{ ($counts['approved'] ?? 0) + ($counts['received'] ?? 0) }}</strong></div></div>
    <div class="stat"><div class="icon green"><i class="fa-solid fa-circle-check"></i></div><div><small>Completed</small><strong>{{ ($counts['refunded'] ?? 0) + ($counts['replaced'] ?? 0) }}</strong></div></div>
    <div class="stat"><div class="icon red"><i class="fa-solid fa-money-bill-transfer"></i></div><div><small>Total Refunded</small><strong>{{ taka($refunded) }}</strong></div></div>
</div>

<div class="status-tabs">
    <a href="{{ route('admin.returns.index', request()->except('status', 'page')) }}" class="{{ request('status') ? '' : 'active' }}">All <span>{{ $counts->sum() }}</span></a>
    @foreach(\App\Models\OrderReturn::STATUSES as $k => [$label])
        <a href="{{ route('admin.returns.index', ['status' => $k]) }}" class="{{ request('status') === $k ? 'active' : '' }}">{{ $label }} <span>{{ $counts[$k] ?? 0 }}</span></a>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <form class="filters" method="GET">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input name="q" class="form-control" placeholder="Return #, order #, name or phone" value="{{ request('q') }}">
            <button class="btn btn-light"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </form>
        <span class="muted">To create a return manually, open the order and click “Create Return”.</span>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Return</th><th>Order</th><th>Customer</th><th>Reason</th><th>Resolution</th><th>Value</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @forelse($returns as $r)
                    <tr>
                        <td><a href="{{ route('admin.returns.show', $r) }}"><strong>#{{ $r->return_number }}</strong></a><div class="muted">by {{ $r->created_by }}</div></td>
                        <td><a href="{{ route('admin.orders.show', $r->order_id) }}">#{{ $r->order?->order_number }}</a></td>
                        <td>{{ $r->name }}<div class="muted">{{ $r->phone }}</div></td>
                        <td>{{ $r->reason }}</td>
                        <td>{{ \App\Models\OrderReturn::RESOLUTIONS[$r->resolution] ?? $r->resolution }}</td>
                        <td>{{ taka($r->items_sum_total ?? 0) }}</td>
                        <td><span class="badge badge-{{ $r->status_badge }}">{{ $r->status_label }}</span></td>
                        <td class="muted">{{ $r->created_at->format('d M Y') }}</td>
                        <td><a href="{{ route('admin.returns.show', $r) }}" class="btn btn-sm btn-icon"><i class="fa-regular fa-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty">No returns.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $returns->links() }}
</div>
@endsection
