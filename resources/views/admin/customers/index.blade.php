@extends('admin.layouts.app')

@section('title', 'Customers')

@section('content')
<div class="card">
    <div class="card-header">
        <form class="filters" method="GET">
            <input name="q" class="form-control" placeholder="Name, email or phone" value="{{ request('q') }}">
            <button class="btn btn-light"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </form>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Orders</th><th>Total Spent</th><th>Joined</th><th></th></tr></thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td><strong>{{ $c->name }}</strong></td>
                        <td>{{ $c->phone }}</td>
                        <td>{{ $c->email }}</td>
                        <td>{{ $c->orders_count }}</td>
                        <td>{{ taka($c->orders_sum_total ?? 0) }}</td>
                        <td class="muted">{{ $c->created_at->format('d M Y') }}</td>
                        <td><div class="actions">
                            <a href="{{ route('admin.customers.show', $c) }}" class="btn btn-sm btn-icon"><i class="fa-regular fa-eye"></i></a>
                            @include('admin.partials.delete', ['action' => route('admin.customers.destroy', $c)])
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">No customers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $customers->links() }}
</div>
@endsection
