@extends('admin.layouts.app')

@section('title', 'Brands')

@section('content')
<div class="card">
    <div class="card-header"><h3>All Brands</h3><a href="{{ route('admin.brands.create') }}" class="btn"><i class="fa-solid fa-plus"></i> Add Brand</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Logo</th><th>Name</th><th>Products</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($brands as $b)
                    <tr>
                        <td><img src="{{ $b->logo_url }}" class="thumb" style="object-fit:contain" alt=""></td>
                        <td><strong>{{ $b->name }}</strong></td>
                        <td>{{ $b->products_count }}</td>
                        <td><span class="badge badge-{{ $b->is_active ? 'success' : 'secondary' }}">{{ $b->is_active ? 'Active' : 'Hidden' }}</span></td>
                        <td><div class="actions">
                            <a href="{{ route('admin.brands.edit', $b) }}" class="btn btn-sm btn-icon"><i class="fa-regular fa-pen-to-square"></i></a>
                            @include('admin.partials.delete', ['action' => route('admin.brands.destroy', $b)])
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No brands yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $brands->links() }}
</div>
@endsection
