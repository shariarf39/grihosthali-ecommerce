@extends('admin.layouts.app')

@section('title', 'Welcome')

@section('content')
@php
    $shortcuts = [
        ['orders.view', 'admin.orders.index', 'fa-solid fa-bag-shopping', 'Orders', 'View and process orders'],
        ['courier.manage', 'admin.shipments.index', 'fa-solid fa-truck-fast', 'Shipments', 'Courier bookings & tracking'],
        ['returns.manage', 'admin.returns.index', 'fa-solid fa-rotate-left', 'Returns', 'Handle returns & refunds'],
        ['customers.manage', 'admin.crm.index', 'fa-solid fa-address-book', 'Customers', 'CRM & follow-ups'],
        ['products.manage', 'admin.products.index', 'fa-solid fa-box', 'Products', 'Manage the catalog'],
        ['inventory.manage', 'admin.inventory.index', 'fa-solid fa-warehouse', 'Inventory', 'Stock in / out'],
        ['marketing.manage', 'admin.coupons.index', 'fa-solid fa-ticket', 'Marketing', 'Coupons & sliders'],
        ['messages.manage', 'admin.messages.index', 'fa-solid fa-inbox', 'Messages', 'Customer enquiries'],
    ];
@endphp
<div class="card welcome-card">
    <div class="card-body">
        <h2 style="margin:0">Hello, {{ auth()->user()->name }} 👋</h2>
        <p class="muted" style="margin:6px 0 0">You are signed in as <strong>{{ auth()->user()->role?->name }}</strong>. Here is what you can work on:</p>
    </div>
</div>
<div class="shortcut-grid">
    @foreach($shortcuts as [$perm, $route, $icon, $title, $text])
        @can($perm)
            <a href="{{ route($route) }}" class="card shortcut">
                <span class="sc-icon"><i class="{{ $icon }}"></i></span>
                <strong>{{ $title }}</strong><small>{{ $text }}</small>
            </a>
        @endcan
    @endforeach
</div>
@endsection
