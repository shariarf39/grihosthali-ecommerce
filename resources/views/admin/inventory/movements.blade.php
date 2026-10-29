@extends('admin.layouts.app')

@section('title', 'Stock History')

@section('content')
@php $orderIds = \App\Models\Order::whereIn('order_number', $movements->pluck('reference')->filter())->pluck('id', 'order_number'); @endphp
<div class="card">
    <div class="card-header">
        <form class="filters" method="GET">
            <select name="product" class="form-control">
                <option value="">All products</option>
                @foreach($products as $p)<option value="{{ $p->id }}" @selected(request('product') == $p->id)>{{ $p->name }}</option>@endforeach
            </select>
            <select name="type" class="form-control">
                <option value="">All movement types</option>
                @foreach(\App\Models\StockMovement::TYPES as $k => [$label])<option value="{{ $k }}" @selected(request('type') === $k)>{{ $label }}</option>@endforeach
            </select>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}" title="From">
            <input type="date" name="to" class="form-control" value="{{ request('to') }}" title="To">
            <button class="btn btn-light"><i class="fa-solid fa-filter"></i> Filter</button>
        </form>
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i> Inventory</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Date</th><th>Product</th><th>Type</th><th>Change</th><th>Stock</th><th>Reference</th><th>Note</th><th>By</th></tr></thead>
            <tbody>
                @forelse($movements as $m)
                    <tr>
                        <td class="muted">{{ $m->created_at->format('d M Y') }}<br>{{ $m->created_at->format('h:i A') }}</td>
                        <td>{{ $m->product?->name ?? 'Deleted product' }}</td>
                        <td><span class="badge badge-{{ $m->badge }}">{{ $m->type_label }}</span></td>
                        <td><strong style="color:{{ $m->quantity >= 0 ? 'var(--success)' : 'var(--danger)' }}">{{ $m->quantity >= 0 ? '+' : '' }}{{ $m->quantity }}</strong></td>
                        <td class="muted">{{ $m->stock_before }} → <strong style="color:var(--title)">{{ $m->stock_after }}</strong></td>
                        <td>
                            @if(isset($orderIds[$m->reference]))<a href="{{ route('admin.orders.show', $orderIds[$m->reference]) }}">#{{ $m->reference }}</a>@else{{ $m->reference ?: '—' }}@endif
                        </td>
                        <td class="muted">{{ $m->note }}@if($m->unit_cost) · cost {{ taka($m->unit_cost) }}@endif</td>
                        <td class="muted">{{ $m->user?->name ?? 'Website' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">No stock movements yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $movements->links() }}
</div>
@endsection
