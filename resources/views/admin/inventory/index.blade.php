@extends('admin.layouts.app')

@section('title', 'Inventory')

@section('content')
<div class="stats">
    <div class="stat"><div class="icon green"><i class="fa-solid fa-boxes-stacked"></i></div><div><small>Total Units in Stock</small><strong>{{ number_format($summary['units']) }}</strong><small>{{ $summary['skus'] }} products</small></div></div>
    <div class="stat"><div class="icon gold"><i class="fa-solid fa-coins"></i></div><div><small>Stock Value (cost)</small><strong>{{ taka($summary['cost_value']) }}</strong><small>Sale value {{ taka($summary['sale_value']) }}</small></div></div>
    <div class="stat"><div class="icon blue"><i class="fa-solid fa-triangle-exclamation"></i></div><div><small>Low Stock</small><strong>{{ $summary['low'] }}</strong><small><a href="{{ route('admin.inventory.index', ['filter' => 'low']) }}">View items</a></small></div></div>
    <div class="stat"><div class="icon red"><i class="fa-solid fa-ban"></i></div><div><small>Out of Stock</small><strong>{{ $summary['out'] }}</strong><small><a href="{{ route('admin.inventory.index', ['filter' => 'out']) }}">View items</a></small></div></div>
</div>

<div class="card">
    <div class="card-header">
        <form class="filters" method="GET">
            <input name="q" class="form-control" placeholder="Search product or SKU" value="{{ request('q') }}">
            <select name="category" class="form-control">
                <option value="">All categories</option>
                @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach
            </select>
            <select name="filter" class="form-control">
                <option value="">All stock levels</option>
                <option value="in" @selected(request('filter') === 'in')>In stock</option>
                <option value="low" @selected(request('filter') === 'low')>Low stock</option>
                <option value="out" @selected(request('filter') === 'out')>Out of stock</option>
            </select>
            <select name="sort" class="form-control">
                <option value="">Stock: low → high</option>
                <option value="stock_desc" @selected(request('sort') === 'stock_desc')>Stock: high → low</option>
                <option value="name" @selected(request('sort') === 'name')>Name A–Z</option>
            </select>
            <button class="btn btn-light"><i class="fa-solid fa-filter"></i> Filter</button>
        </form>
        <div class="actions">
            <a href="{{ route('admin.inventory.movements') }}" class="btn btn-light"><i class="fa-solid fa-clock-rotate-left"></i> Stock History</a>
            <a href="{{ route('admin.inventory.export') }}" class="btn btn-gold"><i class="fa-solid fa-file-csv"></i> Export</a>
        </div>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Product</th><th>Stock</th><th>Alert at</th><th>Cost / Sell</th><th>Stock Value</th><th style="min-width:420px">Update Stock</th></tr></thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td>
                            <div style="display:flex;gap:10px;align-items:center">
                                <img src="{{ $p->image_url }}" class="thumb" alt="">
                                <div><strong>{{ $p->name }}</strong><div class="muted">{{ $p->sku }} · {{ $p->category?->name }}</div></div>
                            </div>
                        </td>
                        <td>
                            <span class="stock-pill {{ $p->stock == 0 ? 'out' : ($p->is_low_stock ? 'low' : 'ok') }}">{{ $p->stock }}</span>
                            <div class="muted">{{ $p->stock == 0 ? 'Out of stock' : ($p->is_low_stock ? 'Low stock' : 'In stock') }}</div>
                        </td>
                        <td>{{ $p->low_stock_alert }}</td>
                        <td>{{ $p->cost_price !== null ? taka($p->cost_price) : '—' }}<div class="muted">{{ taka($p->sell_price) }}</div></td>
                        <td>{{ taka($p->stock * (float) $p->cost_price) }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.inventory.adjust', $p) }}" class="adjust-form">
                                @csrf
                                <select name="action" class="form-control" aria-label="Action">
                                    <option value="purchase">+ Stock In (purchase)</option>
                                    <option value="adjustment_in">+ Adjust up</option>
                                    <option value="adjustment_out">− Adjust down</option>
                                    <option value="damage">− Damage / Loss</option>
                                    <option value="audit">= Set exact count</option>
                                </select>
                                <input type="number" name="quantity" min="0" class="form-control" placeholder="Qty" required aria-label="Quantity">
                                <input type="text" name="note" class="form-control" placeholder="Note / supplier" aria-label="Note">
                                <button class="btn btn-sm"><i class="fa-solid fa-check"></i></button>
                            </form>
                            <a href="{{ route('admin.inventory.movements', ['product' => $p->id]) }}" class="muted" style="font-size:12px"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $products->links() }}
</div>
@endsection
