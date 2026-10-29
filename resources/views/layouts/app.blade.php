<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') | @endif{{ setting('site_name', 'Grihosthali') }}</title>
    <meta name="description" content="@yield('meta_description', setting('meta_description', 'Pure, natural and traditional food products delivered across Bangladesh.'))">
    <meta name="keywords" content="{{ setting('meta_keywords', 'ghee, honey, mustard oil, organic food, Bangladesh') }}">
    <meta property="og:title" content="@yield('title', setting('site_name', 'Grihosthali'))">
    <meta property="og:image" content="@yield('og_image', asset('uploads/sliders/ghee-hero.png'))">
    <link rel="icon" href="{{ setting('favicon') ? upload_url(setting('favicon')) : asset('images/favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Serif+Bengali:wght@600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <style>
        :root {
            --primary: {{ setting('primary_color', '#1f4d36') }};
            --primary-dark: color-mix(in srgb, var(--primary) 80%, black);
            --primary-soft: color-mix(in srgb, var(--primary) 9%, white);
            --secondary: {{ setting('secondary_color', '#c9a24a') }};
        }
    </style>
    @stack('styles')
</head>
<body>

@if(setting('top_notice'))
    <div class="top-notice">
        <div class="container">
            <span class="bn"><i class="fa-solid fa-truck-fast"></i>{{ setting('top_notice') }}</span>
            <div class="tn-links">
                <a href="tel:{{ setting('hotline', setting('phone')) }}"><i class="fa-solid fa-phone"></i>{{ setting('hotline', setting('phone')) }}</a>
                <a href="{{ route('track-order') }}"><i class="fa-solid fa-location-crosshairs"></i>Track Order</a>
            </div>
        </div>
    </div>
@endif

<header class="header">
    <div class="header-middle">
        <div class="container">
            <div class="header-inner">
                <div class="header-left">
                    <button class="mobile-menu-toggle" data-open-menu aria-label="Menu"><i class="fa-solid fa-bars-staggered"></i></button>
                    <a href="{{ route('home') }}" class="logo" aria-label="{{ setting('site_name', 'Grihosthali') }}">
                        @include('partials.logo')
                    </a>
                    <div class="header-search">
                        <form action="{{ route('shop') }}" method="GET">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search for ghee, honey, oil..." autocomplete="off" data-live-search>
                            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                        </form>
                        <ul class="live-search hidden"></ul>
                    </div>
                </div>
                <div class="header-right">
                    <a href="{{ route('track-order') }}" class="header-action hide-mobile">
                        <i class="fa-solid fa-truck-fast"></i><span class="label">Track Order</span>
                    </a>
                    @auth
                        <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('account.index') }}" class="header-action">
                            <i class="fa-regular fa-user"></i><span class="label">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 10) }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="header-action">
                            <i class="fa-regular fa-user"></i><span class="label">Login</span>
                        </a>
                    @endauth
                    <button type="button" class="header-action" data-open-cart>
                        <i class="fa-solid fa-cart-shopping"></i><span class="cart-count">{{ $cartCount }}</span><span class="label">Cart</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <nav class="header-bottom">
        <div class="container">
            <ul class="main-menu">
                <li class="{{ request()->routeIs('home') ? 'active' : '' }}"><a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Home</a></li>
                @foreach($menuCategories as $cat)
                    <li class="{{ request()->is('category/' . $cat->slug) ? 'active' : '' }}">
                        <a href="{{ route('category.show', $cat->slug) }}">{{ $cat->name }} @if($cat->children->count())<i class="fa-solid fa-chevron-down"></i>@endif</a>
                        @if($cat->children->count())
                            <ul class="submenu">
                                @foreach($cat->children as $child)
                                    <li><a href="{{ route('category.show', $child->slug) }}">{{ $child->name }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
                <li class="{{ request()->routeIs('shop') ? 'active' : '' }}"><a href="{{ route('shop') }}">All Products</a></li>
            </ul>
        </div>
    </nav>
</header>

<main>
    @yield('content')
</main>

@include('partials.footer')

{{-- Mobile drawer --}}
<aside class="mobile-drawer" aria-label="Menu">
    <div class="md-head">
        <h4>{{ setting('site_name', 'Grihosthali') }}</h4>
        <button data-close-panel aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="md-search">
        <form action="{{ route('shop') }}" method="GET">
            <input type="text" name="q" placeholder="Search products...">
            <button type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>
    <ul class="md-menu">
        <li><a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Home</a></li>
        <li><a href="{{ route('shop') }}"><i class="fa-solid fa-store"></i> All Products</a></li>
        @foreach($menuCategories as $cat)
            <li><a href="{{ route('category.show', $cat->slug) }}"><img src="{{ $cat->image_url }}" alt=""> {{ $cat->name }}</a></li>
            @foreach($cat->children as $child)
                <li class="sub"><a href="{{ route('category.show', $child->slug) }}">{{ $child->name }}</a></li>
            @endforeach
        @endforeach
        <li><a href="{{ route('track-order') }}"><i class="fa-solid fa-truck-fast"></i> Track Order</a></li>
        <li><a href="{{ route('contact') }}"><i class="fa-solid fa-headset"></i> Contact Us</a></li>
    </ul>
</aside>

{{-- Cart sidebar --}}
<aside class="cart-sidebar" aria-label="Shopping cart">
    <div class="cs-head">
        <h4><i class="fa-solid fa-bag-shopping"></i> Shopping Cart (<span class="cs-title-count">{{ $cartCount }}</span>)</h4>
        <button class="cs-close" data-close-panel aria-label="Close cart"><i class="fa-solid fa-xmark"></i></button>
    </div>
    @if((float) setting('free_shipping_min', 0) > 0)
        <div class="cs-progress hidden">
            <div class="cs-progress-text"></div>
            <div class="bar"><span style="width:0"></span></div>
        </div>
    @endif
    <div class="cs-body">
        <div class="cs-empty"><i class="fa-solid fa-spinner fa-spin"></i></div>
    </div>
    <div class="cs-foot hidden">
        <div class="cs-total"><span>Subtotal</span><strong class="cs-subtotal">৳0</strong></div>
        <div class="cs-actions">
            <a href="{{ route('cart.index') }}" class="btn btn-outline">View Cart</a>
            <a href="{{ route('checkout') }}" class="btn">Checkout <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</aside>
<div class="overlay"></div>

{{-- Floating cart (desktop) like the reference site --}}
<button class="floating-cart" data-open-cart aria-label="Open cart">
    <i class="fa-solid fa-bag-shopping"></i>
    <span class="fc-count">{{ $cartCount }} Items</span>
    <span class="fc-total">৳0</span>
</button>

{{-- Mobile bottom navigation --}}
<nav class="sticky-footer">
    <a href="{{ route('home') }}" class="sticky-link"><i class="fa-solid fa-house"></i><p>Home</p></a>
    <button type="button" class="sticky-link" data-open-menu><i class="fa-solid fa-table-cells-large"></i><p>Categories</p></button>
    <button type="button" class="sticky-link" data-open-cart><i class="fa-solid fa-cart-shopping"></i><span class="cart-count">{{ $cartCount }}</span><p>Cart</p></button>
    <a href="{{ route('track-order') }}" class="sticky-link"><i class="fa-solid fa-truck-fast"></i><p>Track</p></a>
    <a href="{{ auth()->check() ? route('account.index') : route('login') }}" class="sticky-link"><i class="fa-regular fa-user"></i><p>Account</p></a>
</nav>

@if(setting('whatsapp'))
    <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}" target="_blank" rel="noopener" class="float-whatsapp" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
@endif
<button class="scroll-top" aria-label="Scroll to top"><i class="fa-solid fa-arrow-up"></i></button>

@php
    $flash = [];
    if (session('success')) $flash[] = ['type' => 'success', 'message' => session('success')];
    if (session('error')) $flash[] = ['type' => 'error', 'message' => session('error')];
@endphp
<script>
    window.GH = {
        csrf: '{{ csrf_token() }}',
        freeShippingMin: {{ (float) setting('free_shipping_min', 0) }},
        routes: {
            shop: '{{ route('shop') }}',
            cartData: '{{ route('cart.data') }}',
            cartAdd: '{{ route('cart.add') }}',
            cartUpdate: '{{ route('cart.update') }}',
            cartRemove: '{{ route('cart.remove') }}',
            suggest: '{{ route('search.suggest') }}'
        },
        flash: @json($flash)
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
@stack('scripts')
</body>
</html>
