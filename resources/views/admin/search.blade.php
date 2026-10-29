@extends('admin.layouts.app')

@section('title', 'Search')
@section('subtitle', $q ? 'Results for “' . $q . '”' : 'Type at least 2 characters')

@section('content')
@php $total = collect($results)->sum(fn ($c) => $c->count()); @endphp
@if($q && ! $total)
    <div class="card"><div class="card-body empty"><i class="fa-solid fa-magnifying-glass" style="font-size:30px;display:block;margin-bottom:10px"></i>Nothing found for “{{ $q }}”.</div></div>
@endif

<div class="grid-half">
    @if($results['orders']->count())
        <div class="card">
            <div class="card-header"><h3><i class="fa-solid fa-bag-shopping" style="color:var(--gold)"></i> Orders</h3></div>
            <div class="table-wrap"><table class="table">
                @foreach($results['orders'] as $o)
                    <tr><td><a href="{{ route('admin.orders.show', $o) }}"><strong>#{{ $o->order_number }}</strong></a><div class="muted">{{ $o->name }} · {{ $o->phone }}</div></td>
                        <td>{{ taka($o->total) }}</td><td><span class="badge badge-{{ status_badge($o->status) }}">{{ $o->status_label }}</span></td></tr>
                @endforeach
            </table></div>
        </div>
    @endif
    @if($results['customers']->count())
        <div class="card">
            <div class="card-header"><h3><i class="fa-solid fa-address-book" style="color:var(--gold)"></i> Customers</h3></div>
            <div class="table-wrap"><table class="table">
                @foreach($results['customers'] as $c)
                    <tr><td><a href="{{ route('admin.crm.show', $c) }}"><strong>{{ $c->name }}</strong></a><div class="muted">{{ $c->phone }} {{ $c->email }}</div></td>
                        <td class="muted">{{ $c->last_order_at?->diffForHumans() }}</td></tr>
                @endforeach
            </table></div>
        </div>
    @endif
    @if($results['products']->count())
        <div class="card">
            <div class="card-header"><h3><i class="fa-solid fa-box" style="color:var(--gold)"></i> Products</h3></div>
            <div class="table-wrap"><table class="table">
                @foreach($results['products'] as $p)
                    <tr><td><img src="{{ $p->image_url }}" class="thumb" alt=""></td>
                        <td><a href="{{ route('admin.products.edit', $p) }}"><strong>{{ $p->name }}</strong></a><div class="muted">{{ $p->sku }}</div></td>
                        <td>{{ taka($p->sell_price) }}</td><td><span class="badge badge-{{ $p->is_low_stock ? 'warning' : 'success' }}">{{ $p->stock }} in stock</span></td></tr>
                @endforeach
            </table></div>
        </div>
    @endif
    @if($results['shipments']->count())
        <div class="card">
            <div class="card-header"><h3><i class="fa-solid fa-truck-fast" style="color:var(--gold)"></i> Consignments</h3></div>
            <div class="table-wrap"><table class="table">
                @foreach($results['shipments'] as $s)
                    <tr><td><strong>{{ $s->consignment_id }}</strong><div class="muted">{{ $s->courier_label }}</div></td>
                        <td><a href="{{ route('admin.orders.show', $s->order_id) }}">#{{ $s->order?->order_number }}</a></td>
                        <td><span class="badge badge-{{ $s->status_badge }}">{{ $s->status_label }}</span></td></tr>
                @endforeach
            </table></div>
        </div>
    @endif
</div>
@endsection
