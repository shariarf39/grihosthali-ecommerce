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
    <script>
        // Apply the saved mini-sidebar preference before paint to avoid a flash.
        try { if (localStorage.getItem('gh_admin_mini') === '1' && window.innerWidth > 991) document.documentElement.classList.add('sidebar-mini'); } catch (e) {}
    </script>
    @stack('styles')
</head>
<body>
@php
    $me = auth()->user();
    // [label, route, icon, permission, badge]
    $menu = [
        ['Dashboard', [['Dashboard', 'admin.dashboard', 'fa-solid fa-gauge-high', null]], 'fa-solid fa-gauge-high'],
        ['Sales', [
            ['Orders', 'admin.orders.index', 'fa-solid fa-bag-shopping', 'orders.view', $badges['orders']],
            ['Shipments', 'admin.shipments.index', 'fa-solid fa-truck-fast', 'courier.manage'],
            ['Returns', 'admin.returns.index', 'fa-solid fa-rotate-left', 'returns.manage', $badges['returns']],
            ['Reports', 'admin.reports', 'fa-solid fa-chart-line', 'reports.view'],
        ], 'fa-solid fa-cart-shopping'],
        ['Customers', [
            ['CRM Customers', 'admin.crm.index', 'fa-solid fa-address-book', 'customers.manage'],
            ['Follow-ups', 'admin.crm.follow-ups', 'fa-regular fa-bell', 'customers.manage', $badges['follow_ups']],
            ['Registered Users', 'admin.customers.index', 'fa-solid fa-user-check', 'customers.manage'],
            ['Messages', 'admin.messages.index', 'fa-solid fa-inbox', 'messages.manage', $badges['messages']],
            ['Subscribers', 'admin.subscribers.index', 'fa-solid fa-envelope-open-text', 'messages.manage'],
        ], 'fa-solid fa-users'],
        ['Catalog', [
            ['Products', 'admin.products.index', 'fa-solid fa-box', 'products.manage'],
            ['Categories', 'admin.categories.index', 'fa-solid fa-layer-group', 'catalog.manage'],
            ['Brands', 'admin.brands.index', 'fa-solid fa-tags', 'catalog.manage'],
            ['Reviews', 'admin.reviews.index', 'fa-solid fa-star', 'catalog.manage', $badges['reviews']],
        ], 'fa-solid fa-boxes-stacked'],
        ['Inventory', [
            ['Stock', 'admin.inventory.index', 'fa-solid fa-warehouse', 'inventory.manage', $badges['low_stock']],
            ['Stock History', 'admin.inventory.movements', 'fa-solid fa-clock-rotate-left', 'inventory.manage'],
        ], 'fa-solid fa-warehouse'],
        ['Marketing', [
            ['Coupons', 'admin.coupons.index', 'fa-solid fa-ticket', 'marketing.manage'],
            ['Sliders', 'admin.sliders.index', 'fa-solid fa-images', 'marketing.manage'],
            ['Testimonials', 'admin.testimonials.index', 'fa-solid fa-quote-left', 'marketing.manage'],
        ], 'fa-solid fa-bullhorn'],
        ['Website', [
            ['Pages', 'admin.pages.index', 'fa-solid fa-file-lines', 'pages.manage'],
            ['Store Settings', 'admin.settings', 'fa-solid fa-sliders', 'settings.manage'],
        ], 'fa-solid fa-globe'],
        ['Integrations', [
            ['Couriers', 'admin.integrations.couriers', 'fa-solid fa-truck-ramp-box', 'integrations.manage'],
            ['Email (SMTP)', 'admin.integrations.email', 'fa-solid fa-envelope', 'integrations.manage'],
            ['Email Logs', 'admin.integrations.email.logs', 'fa-solid fa-list-check', 'integrations.manage', $badges['mail_failed']],
        ], 'fa-solid fa-plug'],
        ['Staff & Access', [
            ['Admin Users', 'admin.staff.index', 'fa-solid fa-user-shield', 'staff.manage'],
            ['Roles & Permissions', 'admin.roles.index', 'fa-solid fa-key', 'staff.manage'],
            ['Activity Log', 'admin.activity', 'fa-solid fa-fingerprint', 'staff.manage'],
        ], 'fa-solid fa-shield-halved'],
    ];
    $isActive = function (string $route) {
        if (request()->routeIs($route)) {
            return true;
        }
        // Treat create/edit/show pages as part of their list page, except sibling pages that have their own menu item.
        $base = \Illuminate\Support\Str::beforeLast($route, '.');
        return str_ends_with($route, '.index') && request()->routeIs($base . '.*')
            && ! request()->routeIs('admin.crm.follow-ups', 'admin.inventory.movements');
    };
@endphp

<aside class="admin-sidebar" id="sidebar">
    <a href="{{ route('admin.dashboard') }}" class="admin-brand">
        <span class="mark"><i class="fa-solid fa-seedling"></i></span>
        <span class="brand-text"><strong>গৃহস্থালী</strong><small>ADMIN PANEL</small></span>
    </a>

    <nav class="admin-nav">
        @foreach($menu as [$group, $items, $groupIcon])
            @php $visible = array_filter($items, fn ($i) => ! $i[3] || $me->can($i[3])); @endphp
            @continue(! $visible)
            @if($group === 'Dashboard')
                @php $i = reset($visible); @endphp
                <a href="{{ route($i[1]) }}" class="nav-link single {{ $isActive($i[1]) ? 'active' : '' }}" data-tip="{{ $i[0] }}">
                    <i class="{{ $i[2] }}"></i><span>{{ $i[0] }}</span>
                </a>
            @else
                @php
                    $open = collect($visible)->contains(fn ($i) => $isActive($i[1]));
                    $groupBadge = collect($visible)->sum(fn ($i) => (int) ($i[4] ?? 0));
                    $gid = \Illuminate\Support\Str::slug($group);
                @endphp
                <div class="nav-group {{ $open ? 'open' : '' }}" data-group="{{ $gid }}" data-active="{{ $open ? 1 : 0 }}">
                    <button type="button" class="nav-group-toggle" data-tip="{{ $group }}" aria-expanded="{{ $open ? 'true' : 'false' }}">
                        <i class="{{ $groupIcon }}"></i><span>{{ $group }}</span>
                        @if($groupBadge)<em class="count group-count">{{ $groupBadge }}</em>@endif
                        <i class="fa-solid fa-chevron-down caret"></i>
                    </button>
                    <div class="nav-sub"><div class="nav-sub-inner">
                        @foreach($visible as $i)
                            <a href="{{ route($i[1]) }}" class="nav-link {{ $isActive($i[1]) ? 'active' : '' }}">
                                <i class="{{ $i[2] }}"></i><span>{{ $i[0] }}</span>
                                @if(! empty($i[4]))<em class="count">{{ $i[4] }}</em>@endif
                            </a>
                        @endforeach
                    </div></div>
                </div>
            @endif
        @endforeach
    </nav>

    <div class="sidebar-foot">
        <span class="avatar-sm">{{ mb_substr($me->name, 0, 1) }}</span>
        <div class="who"><strong>{{ \Illuminate\Support\Str::limit($me->name, 18) }}</strong><small>{{ $me->role?->name ?? 'Admin' }}</small></div>
    </div>
</aside>
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>

<div class="admin-main">
    <header class="admin-topbar">
        <div class="left">
            <button class="icon-btn" id="sidebar-toggle" aria-label="Toggle menu"><i class="fa-solid fa-bars-staggered"></i></button>
            <div class="page-heading">
                <h1>@yield('title', 'Dashboard')</h1>
                @hasSection('subtitle')<small>@yield('subtitle')</small>@endif
            </div>
        </div>

        <form action="{{ route('admin.search') }}" method="GET" class="global-search" role="search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" name="q" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}" placeholder="Search orders, phone, customers, products…" aria-label="Search admin">
            <kbd>/</kbd>
        </form>

        <div class="right">
            <a href="{{ route('home') }}" target="_blank" class="icon-btn" title="View store"><i class="fa-solid fa-store"></i></a>

            <div class="dropdown-wrap" data-dropdown>
                <button type="button" class="icon-btn" aria-label="Notifications">
                    <i class="fa-regular fa-bell"></i>
                    @if($notifications->sum('count'))<span class="dot">{{ $notifications->sum('count') }}</span>@endif
                </button>
                <div class="dropdown-panel notif-panel">
                    <div class="dp-head">Notifications</div>
                    @forelse($notifications as $n)
                        <a href="{{ $n['url'] }}" class="notif-item">
                            <span class="ni-icon {{ $n['tone'] }}"><i class="{{ $n['icon'] }}"></i></span>
                            <span><strong>{{ $n['count'] }} {{ $n['label'] }}</strong><small>{{ $n['hint'] }}</small></span>
                        </a>
                    @empty
                        <div class="notif-empty"><i class="fa-regular fa-circle-check"></i> You're all caught up</div>
                    @endforelse
                </div>
            </div>

            <div class="dropdown-wrap" data-dropdown>
                <button type="button" class="user-btn">
                    <span class="avatar-sm">{{ mb_substr($me->name, 0, 1) }}</span>
                    <span class="user-meta"><strong>{{ \Illuminate\Support\Str::limit($me->name, 16) }}</strong><small>{{ $me->role?->name ?? 'Admin' }}</small></span>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px"></i>
                </button>
                <div class="dropdown-panel">
                    <a href="{{ route('admin.profile') }}"><i class="fa-regular fa-user"></i> My Profile</a>
                    @can('settings.manage')<a href="{{ route('admin.settings') }}"><i class="fa-solid fa-sliders"></i> Store Settings</a>@endcan
                    @can('staff.manage')<a href="{{ route('admin.staff.index') }}"><i class="fa-solid fa-user-shield"></i> Admin Users</a>@endcan
                    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button><i class="fa-solid fa-right-from-bracket"></i> Logout</button></form>
                </div>
            </div>
        </div>
    </header>

    <main class="admin-content">
        @if(session('success'))<div class="alert alert-success" data-autohide><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>@endif
        @if($errors->any())
            <div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif
        @yield('content')
    </main>

    <footer class="admin-footer">
        <span>© {{ date('Y') }} {{ setting('site_name') }} · Admin Panel</span>
        <span>Signed in as {{ $me->email }}</span>
    </footer>
</div>

<script>
(function () {
    const html = document.documentElement;
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const isMobile = () => window.innerWidth <= 991;
    const store = (k, v) => { try { v === undefined ? localStorage.removeItem(k) : localStorage.setItem(k, v); } catch (e) {} };
    const read = (k) => { try { return localStorage.getItem(k); } catch (e) { return null; } };

    // Hamburger: drawer on mobile, mini sidebar on desktop.
    document.getElementById('sidebar-toggle').addEventListener('click', () => {
        if (isMobile()) {
            sidebar.classList.toggle('open');
            backdrop.classList.toggle('show');
        } else {
            html.classList.toggle('sidebar-mini');
            store('gh_admin_mini', html.classList.contains('sidebar-mini') ? '1' : '0');
        }
    });
    backdrop.addEventListener('click', () => { sidebar.classList.remove('open'); backdrop.classList.remove('show'); });

    // Collapsible menu groups; remember which ones the user opened.
    let opened = [];
    try { opened = JSON.parse(read('gh_admin_groups') || '[]'); } catch (e) {}
    document.querySelectorAll('.nav-group').forEach((g) => {
        if (g.dataset.active !== '1' && opened.includes(g.dataset.group)) g.classList.add('open');
        g.querySelector('.nav-group-toggle').addEventListener('click', () => {
            if (html.classList.contains('sidebar-mini') && !isMobile()) { html.classList.remove('sidebar-mini'); store('gh_admin_mini', '0'); }
            g.classList.toggle('open');
            g.querySelector('.nav-group-toggle').setAttribute('aria-expanded', g.classList.contains('open'));
            const now = [...document.querySelectorAll('.nav-group.open')].map((x) => x.dataset.group);
            store('gh_admin_groups', JSON.stringify(now));
        });
    });

    // Dropdowns (notifications, user menu).
    document.querySelectorAll('[data-dropdown]').forEach((wrap) => {
        wrap.querySelector('button').addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('[data-dropdown].show').forEach((o) => o !== wrap && o.classList.remove('show'));
            wrap.classList.toggle('show');
        });
    });
    document.addEventListener('click', (e) => {
        document.querySelectorAll('[data-dropdown].show').forEach((o) => { if (!o.contains(e.target)) o.classList.remove('show'); });
    });

    // "/" focuses global search.
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            document.querySelector('.global-search input').focus();
        }
    });

    // Confirm destructive actions; preview chosen images; tabs; fade success alerts.
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
    document.querySelectorAll('[data-autohide]').forEach((el) => setTimeout(() => { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 5000));
})();
</script>
@stack('scripts')
</body>
</html>
