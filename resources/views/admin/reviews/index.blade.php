@extends('admin.layouts.app')

@section('title', 'Product Reviews')

@section('content')
<div class="status-tabs">
    <a href="{{ route('admin.reviews.index') }}" class="{{ request('status') ? '' : 'active' }}">All</a>
    <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}" class="{{ request('status') === 'pending' ? 'active' : '' }}">Pending</a>
    <a href="{{ route('admin.reviews.index', ['status' => 'approved']) }}" class="{{ request('status') === 'approved' ? 'active' : '' }}">Approved</a>
</div>
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Product</th><th>Customer</th><th>Rating</th><th>Review</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($reviews as $r)
                    <tr>
                        <td>{{ $r->product?->name }}</td>
                        <td>{{ $r->name }}<div class="muted">{{ $r->email }}</div></td>
                        <td style="color:#f5a623;white-space:nowrap">@for($i = 1; $i <= 5; $i++)<i class="fa-{{ $i <= $r->rating ? 'solid' : 'regular' }} fa-star"></i>@endfor</td>
                        <td style="max-width:360px">{{ $r->comment }}<div class="muted">{{ $r->created_at->diffForHumans() }}</div></td>
                        <td><span class="badge badge-{{ $r->is_approved ? 'success' : 'warning' }}">{{ $r->is_approved ? 'Approved' : 'Pending' }}</span></td>
                        <td><div class="actions">
                            <form method="POST" action="{{ route('admin.reviews.toggle', $r) }}">@csrf
                                <button class="btn btn-sm {{ $r->is_approved ? 'btn-light' : '' }}">{{ $r->is_approved ? 'Hide' : 'Approve' }}</button>
                            </form>
                            @include('admin.partials.delete', ['action' => route('admin.reviews.destroy', $r)])
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No reviews.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $reviews->links() }}
</div>
@endsection
