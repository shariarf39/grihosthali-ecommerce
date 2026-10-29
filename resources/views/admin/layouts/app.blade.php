<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ setting('site_name', 'Grihosthali') }} Admin</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600&family=Noto+Serif+Bengali:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    @stack('styles')
</head>
<body>
@php
    $nav = [
        ['Main', null, null],
        ['Dashboard', 'admin.dashboard', 'fa-solid fa-gauge-high'],
        ['Orders', 'admin.orders.index', 'fa-solid fa-bag-shopping', $pendingOrders],
        ['Reports', 'admin.reports', 'fa-solid fa-chart-line'],
        ['Catalog', null, null],
        ['Products', 'admin.products.index', 'fa-solid fa-box'],
        ['Categories', 'admin.categories.index', 'fa-solid fa-layer-group'],
        ['Brands', 'admin.brands.index', 'fa-solid fa-tags'],
        ['Reviews', 'admin.reviews.index', 'fa-solid fa-star'],
        ['Marketing', null, null],
        ['Coupons', 'admin.coupons.index', 'fa-solid fa-ticket'],
        ['Sliders', 'admin.sliders.index', 'fa-solid fa-images'],
        ['Testimonials', 'admin.testimonials.index', 'fa-solid fa-quote-left'],
        ['Subscribers', 'admin.subscribers.index', 'fa-solid fa-envelope-open-text'],
        ['People', null, null],
        ['Customers', 'admin.customers.index', 'fa-solid fa-users'],
        ['Messages', 'admin.messages.index', 'fa-solid fa-inbox'],
        ['Site', null, null],
        ['Pages', 'admin.pages.index', 'fa-solid fa-file-lines'],
        ['Settings', 'admin.settings', 'fa-solid fa-gear'],
    ];
@endphp
<aside class="admin-sidebar" id="sidebar">
    <a href="{{ route('admin.dashboard') }}" class="admin-brand">
        <span class="mark"><i class="fa-solid fa-seedling"></i></span>
        <span><strong>গৃহস্থালী</strong><small>ADMIN PANEL</small></span>
    </a>
    <ul class="admin-nav">
        @foreach($nav as $item)
            @if(! $item[1])
                <li class="nav-label">{{ $item[0] }}</li>
            @else
                @php $base = \Illuminate\Support\Str::beforeLast($item[1], '.'); @endphp
                <li>
                    <a href="{{ route($item[1]) }}" class="{{ request()->routeIs($item[1]) || (str_ends_with($item[1], '.index') && request()->routeIs($base . '.*')) ? 'active' : '' }}">
                        <i class="{{ $item[2] }}"></i> {{ $item[0] }}
                        @if(! empty($item[3]))<span class="count">{{ $item[3] }}</span>@endif
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</aside>

<div class="admin-main">
    <header class="admin-topbar">
        <div class="left">
            <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
            <h1>@yield('title', 'Dashboard')</h1>
        </div>
        <div class="right">
            <a href="{{ route('home') }}" target="_blank" class="topbar-link"><i class="fa-solid fa-arrow-up-right-from-square"></i> <span>View Site</span></a>
            <div class="admin-user" tabindex="0">
                <span class="av">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                <span class="topbar-link"><span>{{ auth()->user()->name }}</span> <i class="fa-solid fa-chevron-down" style="font-size:10px"></i></span>
                <div class="dropdown">
                    <a href="{{ route('admin.profile') }}"><i class="fa-regular fa-user"></i> Profile</a>
                    <a href="{{ route('admin.settings') }}"><i class="fa-solid fa-gear"></i> Settings</a>
                    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button><i class="fa-solid fa-right-from-bracket"></i> Logout</button></form>
                </div>
            </div>
        </div>
    </header>

    <div class="admin-content">
        @if(session('success'))<div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())
            <div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif
        @yield('content')
    </div>
</div>

<script>
    // Confirm destructive actions and preview chosen images.
    document.addEventListener('submit', (e) => {
        const msg = e.target.dataset.confirm;
        if (msg && !confirm(msg)) e.preventDefault();
    });
    document.addEventListener('change', (e) => {
        const input = e.target;
        if (input.matches('input[type=file][data-preview]') && input.files[0]) {
            const img = document.querySelector(input.dataset.preview);
            if (img) { img.src = URL.createObjectURL(input.files[0]); img.style.display = 'block'; }
        }
    });
    document.querySelectorAll('.tabs button').forEach((b) => b.addEventListener('click', () => {
        document.querySelectorAll('.tabs button').forEach((x) => x.classList.toggle('active', x === b));
        document.querySelectorAll('.tab-pane').forEach((p) => p.classList.toggle('active', p.id === b.dataset.tab));
    }));
</script>
@stack('scripts')
</body>
</html>
