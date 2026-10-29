@extends('layouts.app')

@section('title', 'My Account')

@section('content')
@include('partials.breadcrumb', ['items' => ['My Account' => null]])

<div class="container">
    <div class="account-layout">
        <aside>
            <div class="account-card account-user">
                <div class="av">{{ mb_substr($user->name, 0, 1) }}</div>
                <h4 style="margin:0">{{ $user->name }}</h4>
                <p style="margin:4px 0 14px">{{ $user->phone }}<br>{{ $user->email }}</p>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline btn-block"><i class="fa-solid fa-right-from-bracket"></i> Logout</button></form>
            </div>
            <div class="account-card">
                <h4>Profile</h4>
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('account.profile') }}">
                    @csrf
                    <div class="form-group"><label>Name</label><input name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
                    <div class="form-group"><label>Phone</label><input name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required></div>
                    <div class="form-group"><label>Email</label><input name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
                    <div class="form-group"><label>Address</label><textarea name="address" class="form-control" rows="2">{{ old('address', $user->address) }}</textarea></div>
                    <button class="btn btn-block">Save Profile</button>
                </form>
            </div>
            <div class="account-card">
                <h4>Change Password</h4>
                <form method="POST" action="{{ route('account.password') }}">
                    @csrf
                    <div class="form-group"><label>Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                    <div class="form-group"><label>New Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="form-group"><label>Confirm Password</label><input type="password" name="password_confirmation" class="form-control" required></div>
                    <button class="btn btn-dark btn-block">Update Password</button>
                </form>
            </div>
        </aside>
        <div>
            <div class="account-card">
                <h3>My Orders</h3>
                @if($orders->count())
                    <div class="table-wrap">
                        <table class="table">
                            <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td><strong>#{{ $order->order_number }}</strong></td>
                                        <td>{{ $order->created_at->format('d M Y') }}</td>
                                        <td>{{ $order->items_count }}</td>
                                        <td>{{ taka($order->total) }}</td>
                                        <td><span class="badge badge-{{ status_badge($order->status) }}">{{ $order->status_label }}</span></td>
                                        <td><a href="{{ route('account.order', $order->order_number) }}" class="btn btn-outline" style="padding:6px 12px">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $orders->links() }}
                @else
                    <div class="empty-state"><i class="fa-solid fa-receipt"></i><p>You haven't placed any orders yet.</p><a href="{{ route('shop') }}" class="btn">Start Shopping</a></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
