@extends('admin.layouts.app')

@section('title', 'Categories')

@section('content')
<div class="card">
    <div class="card-header"><h3>All Categories</h3><a href="{{ route('admin.categories.create') }}" class="btn"><i class="fa-solid fa-plus"></i> Add Category</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Image</th><th>Name</th><th>Parent</th><th>Products</th><th>Order</th><th>Menu</th><th>Home Section</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($categories as $c)
                    <tr>
                        <td><img src="{{ $c->image_url }}" class="thumb" alt=""></td>
                        <td><strong>{{ $c->name }}</strong><div class="muted">/{{ $c->slug }}</div></td>
                        <td>{{ $c->parent?->name ?? '—' }}</td>
                        <td>{{ $c->products_count }}</td>
                        <td>{{ $c->sort_order }}</td>
                        <td>{!! $c->show_in_menu ? '<i class="fa-solid fa-check" style="color:var(--success)"></i>' : '—' !!}</td>
                        <td>{!! $c->show_on_home ? '<i class="fa-solid fa-check" style="color:var(--success)"></i>' : '—' !!}</td>
                        <td><span class="badge badge-{{ $c->is_active ? 'success' : 'secondary' }}">{{ $c->is_active ? 'Active' : 'Hidden' }}</span></td>
                        <td><div class="actions">
                            <a href="{{ route('admin.categories.edit', $c) }}" class="btn btn-sm btn-icon"><i class="fa-regular fa-pen-to-square"></i></a>
                            @include('admin.partials.delete', ['action' => route('admin.categories.destroy', $c), 'confirm' => 'Delete this category? Its products will become uncategorized.'])
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $categories->links() }}
</div>
@endsection
