@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')
<div class="card">
    <div class="card-header">
        <form class="filters" method="GET">
            <input name="q" class="form-control" placeholder="Search name or SKU" value="{{ request('q') }}">
            <select name="category" class="form-control">
                <option value="">All categories</option>
                @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach
            </select>
            <select name="status" class="form-control">
                <option value="">Any status</option>
                <option value="1" @selected(request('status') === '1')>Active</option>
                <option value="0" @selected(request('status') === '0')>Hidden</option>
            </select>
            <select name="stock" class="form-control">
                <option value="">Any stock</option>
                <option value="low" @selected(request('stock') === 'low')>Low stock</option>
            </select>
            <button class="btn btn-light"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
        </form>
        <a href="{{ route('admin.products.create') }}" class="btn"><i class="fa-solid fa-plus"></i> Add Product</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Sold</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td><img src="{{ $p->image_url }}" class="thumb" alt=""></td>
                        <td>
                            <strong>{{ $p->name }}</strong>
                            <div class="muted">
                                @if($p->flag)<span class="badge badge-primary">{{ $p->flag }}</span>@endif
                                @if($p->is_top_selling)<span class="badge badge-warning">Top selling</span>@endif
                                {{ $p->sku }}
                            </div>
                        </td>
                        <td>{{ $p->category?->name ?? '—' }}</td>
                        <td>
                            {{ taka($p->sell_price) }}
                            @if($p->hasDiscount())<div class="muted"><del>{{ taka($p->price) }}</del> −{{ $p->save_percent }}%</div>@endif
                        </td>
                        <td><span class="badge badge-{{ $p->stock == 0 ? 'danger' : ($p->is_low_stock ? 'warning' : 'success') }}">{{ $p->stock }}</span></td>
                        <td>{{ $p->sold_count }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.products.toggle', $p) }}">@csrf
                                <button class="badge badge-{{ $p->is_active ? 'success' : 'secondary' }}" style="border:0;cursor:pointer">{{ $p->is_active ? 'Active' : 'Hidden' }}</button>
                            </form>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('product.show', $p->slug) }}" target="_blank" class="btn btn-sm btn-light btn-icon" title="View"><i class="fa-regular fa-eye"></i></a>
                                <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-sm btn-icon" title="Edit"><i class="fa-regular fa-pen-to-square"></i></a>
                                @include('admin.partials.delete', ['action' => route('admin.products.destroy', $p)])
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $products->links() }}
</div>
@endsection
