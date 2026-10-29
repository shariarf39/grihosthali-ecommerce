@extends('admin.layouts.app')

@section('title', 'Shipments')
@section('subtitle', 'Every parcel booked with a courier')

@section('content')
<div class="stats" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat"><div class="icon blue"><i class="fa-solid fa-truck-fast"></i></div><div><small>In transit / pending</small><strong>{{ $summary['active'] }}</strong></div></div>
    <div class="stat"><div class="icon gold"><i class="fa-solid fa-money-bill-wave"></i></div><div><small>COD to be collected</small><strong>{{ taka($summary['cod_pending']) }}</strong></div></div>
    <div class="stat"><div class="icon green"><i class="fa-solid fa-plug"></i></div><div><small>Active couriers</small><strong>{{ count(app(\App\Services\Couriers\CourierManager::class)->available()) }}</strong>
        @can('integrations.manage')<small><a href="{{ route('admin.integrations.couriers') }}">Configure</a></small>@endcan</div></div>
</div>

<div class="card">
    <div class="card-header">
        <form class="filters" method="GET">
            <input name="q" class="form-control" placeholder="Consignment, order # or phone" value="{{ request('q') }}">
            <select name="courier" class="form-control">
                <option value="">All couriers</option>
                @foreach(\App\Services\Couriers\CourierManager::LABELS as $k => $l)<option value="{{ $k }}" @selected(request('courier') === $k)>{{ $l }}</option>@endforeach
            </select>
            <select name="status" class="form-control">
                <option value="">Any status</option>
                @foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords(str_replace(['_', '-'], ' ', $s)) }}</option>@endforeach
            </select>
            <button class="btn btn-light"><i class="fa-solid fa-filter"></i> Filter</button>
        </form>
        <form method="POST" action="{{ route('admin.shipments.sync-all') }}">@csrf
            <button class="btn"><i class="fa-solid fa-arrows-rotate"></i> Sync all statuses</button>
        </form>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Courier</th><th>Consignment</th><th>Order</th><th>Customer</th><th>COD</th><th>Status</th><th>Synced</th><th></th></tr></thead>
            <tbody>
                @forelse($shipments as $s)
                    <tr style="{{ $s->is_active ? '' : 'opacity:.55' }}">
                        <td><span class="courier-logo sm {{ $s->courier }}">{{ mb_substr($s->courier_label, 0, 1) }}</span> {{ $s->courier_label }}</td>
                        <td><strong>{{ $s->consignment_id }}</strong>@if($s->tracking_code && $s->tracking_code !== $s->consignment_id)<div class="muted">{{ $s->tracking_code }}</div>@endif</td>
                        <td><a href="{{ route('admin.orders.show', $s->order_id) }}">#{{ $s->order?->order_number }}</a></td>
                        <td>{{ $s->order?->name }}<div class="muted">{{ $s->order?->phone }}</div></td>
                        <td>{{ taka($s->cod_amount) }}</td>
                        <td><span class="badge badge-{{ $s->status_badge }}">{{ $s->status_label }}</span>@unless($s->is_active)<div class="muted">detached</div>@endunless</td>
                        <td class="muted">{{ $s->last_synced_at?->diffForHumans() ?? '—' }}</td>
                        <td><div class="actions">
                            <form method="POST" action="{{ route('admin.shipments.sync', $s) }}">@csrf<button class="btn btn-sm btn-light btn-icon" title="Refresh status"><i class="fa-solid fa-arrows-rotate"></i></button></form>
                            @if($s->tracking_url)<a href="{{ $s->tracking_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-light btn-icon" title="Track"><i class="fa-solid fa-location-dot"></i></a>@endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">No shipments yet. Open an order and click “Send to courier”, or select orders on the Orders page.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $shipments->links() }}
</div>
@endsection
