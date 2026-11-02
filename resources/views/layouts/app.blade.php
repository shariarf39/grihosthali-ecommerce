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
    <meta property="og:image" content="@yield('og_image', setting('share_image') ? upload_url(setting('share_image')) : asset('uploads/sliders/cover-grihosthali.jpg'))">
    <link rel="icon" href="{{ setting('favicon') ? upload_url(setting('favicon')) : asset('images/favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ setting('app_icon') ? upload_url(setting('app_icon')) : asset('uploads/brand/icon-192.png') }}">
    <meta name="theme-color" content="{{ setting('primary_color', '#084645') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Serif+Bengali:wght@600;700&family=Playfair+Display:wght@500;600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        // Show the branded loader only on the first page of each visit.
        try {
            if (sessionStorage.getItem('gh_seen')) document.documentElement.classList.add('no-preloader');
            else sessionStorage.setItem('gh_seen', '1');
        } catch (e) { document.documentElement.classList.add('no-preloader'); }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/motion.css') }}?v={{ filemtime(public_path('css/motion.css')) }}">
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
<body data-cursor-trail="{{ setting('cursor_trail') ? 1 : 0 }}" data-cursor-style="{{ setting('cursor_style') }}">
{{-- Branded loading screen; fades out as soon as the page is ready (see motion.js). --}}
<div class="preloader" aria-hidden="true">
    <div class="pl-inner">
        @if(setting('logo'))
            <img class="pl-logo" src="{{ upload_url(setting('logo')) }}" alt="{{ setting('site_name') }}">
        @else
            <span class="pl-leaf"><i class="fa-solid fa-seedling"></i></span>
            <span class="pl-name">গৃহস্থালী</span>
        @endif
        <span class="pl-bar"><span></span></span>
    </div>
</div>
{{-- Custom cursor (desktop mouse only). Style chosen in Admin → Store Settings → Homepage; trail drawn by motion.js. --}}
<div class="cursor-dot" aria-hidden="true">
    {{-- Honey style: glossy golden arrow --}}
    <svg class="cur-arrow" viewBox="0 0 32 32" width="32" height="32">
        <defs>
            <linearGradient id="curHoney" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#fff1b8"/><stop offset=".45" stop-color="#f5b52e"/><stop offset="1" stop-color="#d9661a"/>
            </linearGradient>
        </defs>
        <path d="M4 2.5v23.2l6.2-5.6 4.1 9.2 4.3-1.9-4.1-9h8.4Z" fill="url(#curHoney)" stroke="#173d2a" stroke-width="1.7" stroke-linejoin="round"/>
        <path d="M6.2 7.2v11.3l3-2.7" fill="none" stroke="#fff" stroke-width="1.4" stroke-linecap="round" opacity=".75"/>
    </svg>
    {{-- Honey style: pointing hand for links & buttons --}}
    <svg class="cur-hand" viewBox="0 0 32 32" width="32" height="32">
        <defs>
            <linearGradient id="curHoneyHand" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#fff1b8"/><stop offset=".5" stop-color="#f5b52e"/><stop offset="1" stop-color="#d9661a"/>
            </linearGradient>
        </defs>
        <path d="M11 4.2c0-1.5 1.1-2.6 2.5-2.6S16 2.7 16 4.2v9h.4c0-1.3 1-2.3 2.3-2.3S21 12 21 13.3v.6c.3-1.1 1.2-1.9 2.3-1.9 1.3 0 2.3 1 2.3 2.3v1.4c.3-.9 1.1-1.5 2.1-1.5 1.3 0 2.3 1 2.3 2.3v6.4c0 4.6-3.7 8.3-8.3 8.3h-3.3c-2.6 0-5-1.2-6.6-3.2l-5.5-7c-.8-1-.6-2.5.4-3.3 1-.8 2.4-.7 3.2.3l1.1 1.3Z" fill="url(#curHoneyHand)" stroke="#173d2a" stroke-width="1.6" stroke-linejoin="round"/>
        <path d="M16 13.4v5.2M21 14v4.6M25.6 15.6v3" stroke="#173d2a" stroke-width="1.3" stroke-linecap="round" opacity=".55"/>
    </svg>
    {{-- Leaf style --}}
    <svg class="cur-leaf" viewBox="0 0 24 24" width="22" height="22"><path d="M3 3c10 0 17 6 18 18C9 20 3 13 3 3Z" fill="currentColor"/><path d="M3.5 3.5 14 14" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/></svg>
</div>
<div class="cursor-ring" aria-hidden="true"></div>

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
                        <i class="fa-solid fa-map-location-dot"></i><span class="label">Track Order</span>
                    </a>
                    @auth
                        <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('account.index') }}" class="header-action">
                            <i class="fa-regular fa-user"></i><span class="label">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 10) }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="header-action">
                            <i class="fa-regular fa-user"></i><span class="label">Sign In</span>
                        </a>
                    @endauth
                    <a href="{{ route('wishlist.index') }}" class="header-action">
                        <i class="fa-regular fa-heart"></i><span class="wishlist-count count-badge {{ $wishlistCount ? '' : 'is-empty' }}">{{ $wishlistCount }}</span><span class="label">Wishlist</span>
                    </a>
                    <button type="button" class="header-action" data-open-cart>
                        <i class="fa-solid fa-cart-shopping"></i><span class="cart-count">{{ $cartCount }}</span><span class="label">Cart</span>
                    </button>
                    <div class="header-more hide-mobile">
                        <button type="button" class="header-action" aria-haspopup="true" aria-expanded="false">
                            <i class="fa-solid fa-bars-staggered"></i><span class="label">More</span>
                        </button>
                        @include('partials.more-menu')
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- Category bar sits outside <header> so it can stay pinned to the top while scrolling. --}}
<nav class="header-bottom" aria-label="Categories">
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
                <li class="menu-all {{ request()->routeIs('shop') ? 'active' : '' }}"><a href="{{ route('shop') }}">All Products</a></li>
            </ul>
            {{-- Shown only while the bar is pinned, so search/wishlist/cart stay one tap away. --}}
            <div class="sticky-actions">
                <a href="{{ route('shop') }}" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></a>
                <a href="{{ route('wishlist.index') }}" aria-label="Wishlist"><i class="fa-regular fa-heart"></i><span class="wishlist-count count-badge {{ $wishlistCount ? '' : 'is-empty' }}">{{ $wishlistCount }}</span></a>
                <button type="button" data-open-cart aria-label="Cart"><i class="fa-solid fa-cart-shopping"></i><span class="cart-count">{{ $cartCount }}</span></button>
            </div>
        </div>
    </nav>

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
        <li><a href="{{ route('wishlist.index') }}"><i class="fa-regular fa-heart"></i> Wishlist</a></li>
        <li><a href="{{ route('track-order') }}"><i class="fa-solid fa-truck-fast"></i> Track Order</a></li>
        <li><a href="{{ route('page.show', 'faqs') }}"><i class="fa-regular fa-circle-question"></i> FAQs</a></li>
        <li><a href="{{ route('page.show', 'about-us') }}"><i class="fa-regular fa-id-badge"></i> About Us</a></li>
        <li><a href="{{ route('contact') }}"><i class="fa-solid fa-headset"></i> Contact Us</a></li>
        @if(setting('hotline', setting('phone')))<li><a href="tel:{{ setting('hotline', setting('phone')) }}"><i class="fa-solid fa-phone-volume"></i> Call Us</a></li>@endif
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
    <a href="{{ route('wishlist.index') }}" class="sticky-link"><i class="fa-regular fa-heart"></i><span class="wishlist-count count-badge {{ $wishlistCount ? '' : 'is-empty' }}">{{ $wishlistCount }}</span><p>Wishlist</p></a>
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
            suggest: '{{ route('search.suggest') }}',
            wishlistToggle: '{{ route('wishlist.toggle') }}'
        },
        wishlist: @json($wishlistIds),
        flash: @json($flash)
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11.1.4/swiper-bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
<script src="{{ asset('js/motion.js') }}?v={{ filemtime(public_path('js/motion.js')) }}"></script>
@stack('scripts')
</body>
</html>
