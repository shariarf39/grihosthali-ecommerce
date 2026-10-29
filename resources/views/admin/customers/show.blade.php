@extends('admin.layouts.app')

@section('title', $user->name)

@section('content')
<div class="grid-2">
    <div class="card">
        <div class="card-header"><h3>Orders</h3></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Order</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($orders as $o)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $o) }}">#{{ $o->order_number }}</a></td>
                            <td>{{ taka($o->total) }}</td>
                            <td><span class="badge badge-{{ status_badge($o->status) }}">{{ $o->status_label }}</span></td>
                            <td class="muted">{{ $o->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">No orders.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>
    <div class="card">
        <div class="card-header"><h3>Profile</h3></div>
        <div class="card-body">
            <ul class="info-list">
                <li><span>Name</span><span>{{ $user->name }}</span></li>
                <li><span>Phone</span><span>{{ $user->phone }}</span></li>
                <li><span>Email</span><span>{{ $user->email }}</span></li>
                <li><span>Address</span><span>{{ $user->address ?: '—' }}</span></li>
                <li><span>Joined</span><span>{{ $user->created_at->format('d M Y') }}</span></li>
            </ul>
        </div>
    </div>
</div>
@endsection
