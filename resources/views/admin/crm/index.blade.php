@extends('admin.layouts.app')

@section('title', 'Customers (CRM)')

@section('content')
@php $seg = $stats['segments']; @endphp
<div class="stats">
    <div class="stat"><div class="icon green"><i class="fa-solid fa-users"></i></div><div><small>Total Customers</small><strong>{{ $stats['total'] }}</strong><small>Repeat rate {{ $stats['repeat_rate'] }}%</small></div></div>
    <div class="stat"><div class="icon gold"><i class="fa-solid fa-crown"></i></div><div><small>VIP Customers</small><strong>{{ $seg['VIP'] ?? 0 }}</strong><small>Spent ≥ {{ taka(setting('crm_vip_amount', 10000)) }}</small></div></div>
    <div class="stat"><div class="icon blue"><i class="fa-solid fa-user-clock"></i></div><div><small>At Risk (inactive)</small><strong>{{ $seg['At Risk'] ?? 0 }}</strong><small>No order in {{ setting('crm_inactive_days', 90) }} days</small></div></div>
    <div class="stat"><div class="icon red"><i class="fa-regular fa-bell"></i></div><div><small>Follow-ups Due</small><strong>{{ $stats['follow_ups_due'] }}</strong><small><a href="{{ route('admin.crm.follow-ups') }}">Open task list</a></small></div></div>
</div>

<div class="status-tabs">
    <a href="{{ route('admin.crm.index', request()->except('segment', 'page')) }}" class="{{ request('segment') ? '' : 'active' }}">All <span>{{ $stats['total'] }}</span></a>
    @foreach(['New', 'Repeat', 'VIP', 'At Risk', 'Blocked'] as $s)
        <a href="{{ route('admin.crm.index', array_merge(request()->except('page'), ['segment' => $s])) }}" class="{{ request('segment') === $s ? 'active' : '' }}">{{ $s }} <span>{{ $seg[$s] ?? 0 }}</span></a>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <form class="filters" method="GET">
            <input type="hidden" name="segment" value="{{ request('segment') }}">
            <input name="q" class="form-control" placeholder="Name, phone or email" value="{{ request('q') }}">
            <select name="tag" class="form-control">
                <option value="">Any tag</option>
                @foreach(\App\Models\CrmCustomer::TAGS as $t)<option @selected(request('tag') === $t)>{{ $t }}</option>@endforeach
            </select>
            <select name="sort" class="form-control">
                <option value="">Recent order first</option>
                <option value="spent" @selected(request('sort') === 'spent')>Top spenders</option>
                <option value="orders" @selected(request('sort') === 'orders')>Most orders</option>
                <option value="name" @selected(request('sort') === 'name')>Name A–Z</option>
            </select>
            <button class="btn btn-light"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </form>
        <a href="{{ route('admin.crm.export') }}" class="btn btn-gold"><i class="fa-solid fa-file-csv"></i> Export CSV</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Customer</th><th>Segment</th><th>Orders</th><th>Success</th><th>Total Spent</th><th>Last Order</th><th>Tags</th><th></th></tr></thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td>
                            <div style="display:flex;gap:10px;align-items:center">
                                <span class="avatar-sm">{{ mb_substr($c->name, 0, 1) }}</span>
                                <div><a href="{{ route('admin.crm.show', $c) }}"><strong>{{ $c->name }}</strong></a><div class="muted"><a href="tel:{{ $c->phone }}">{{ $c->phone }}</a> {{ $c->user_id ? '· Registered' : '· Guest' }}</div></div>
                            </div>
                        </td>
                        <td><span class="badge badge-{{ \App\Models\CrmCustomer::segmentBadge($c->segment) }}">{{ $c->segment }}</span></td>
                        <td>{{ $c->orders_count }}</td>
                        <td>
                            @if($c->success_rate !== null)
                                <span class="badge badge-{{ $c->success_rate >= 80 ? 'success' : ($c->success_rate >= 50 ? 'warning' : 'danger') }}">{{ $c->success_rate }}%</span>
                            @else <span class="muted">—</span> @endif
                        </td>
                        <td><strong>{{ taka($c->total_spent ?? 0) }}</strong></td>
                        <td class="muted">{{ $c->last_order_at?->diffForHumans() ?? '—' }}</td>
                        <td>@foreach($c->tags ?? [] as $t)<span class="tag-chip">{{ $t }}</span>@endforeach</td>
                        <td><div class="actions">
                            <a href="{{ route('admin.crm.show', $c) }}" class="btn btn-sm btn-icon" title="Open profile"><i class="fa-regular fa-eye"></i></a>
                            @if($wa = preg_replace('/^0/', '880', $c->phone))
                                <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="btn btn-sm btn-light btn-icon" title="WhatsApp"><i class="fa-brands fa-whatsapp" style="color:#25d366"></i></a>
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">No customers yet. Customers are added automatically when orders are placed.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $customers->links() }}
</div>
@endsection
