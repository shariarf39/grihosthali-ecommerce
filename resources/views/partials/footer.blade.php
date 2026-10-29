@php
    $footerCategories = \App\Models\Category::active()->whereNull('parent_id')->orderBy('sort_order')->take(6)->get();
    $footerPages = \App\Models\Page::where('is_active', true)->orderBy('title')->get(['title', 'slug']);
@endphp
<footer class="footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-about">
                <a href="{{ route('home') }}" class="logo footer-logo">@include('partials.logo')</a>
                <p class="f-about-text">{{ setting('footer_about', 'Grihosthali brings pure, natural and traditional food from the villages of Bangladesh straight to your kitchen.') }}</p>
                <ul class="footer-contact">
                    @if(setting('address'))<li><i class="fa-solid fa-location-dot"></i><span>{{ setting('address') }}</span></li>@endif
                    @if(setting('phone'))<li><i class="fa-solid fa-phone"></i><a href="tel:{{ setting('phone') }}">{{ setting('phone') }}</a></li>@endif
                    @if(setting('email'))<li><i class="fa-regular fa-envelope"></i><a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a></li>@endif
                </ul>
                <ul class="footer-social">
                    @foreach(['facebook' => 'fa-facebook-f', 'instagram' => 'fa-instagram', 'youtube' => 'fa-youtube', 'tiktok' => 'fa-tiktok'] as $key => $icon)
                        @if(setting($key))
                            <li><a href="{{ setting($key) }}" target="_blank" rel="noopener" aria-label="{{ $key }}"><i class="fa-brands {{ $icon }}"></i></a></li>
                        @endif
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="footer-widget-title">Information</h4>
                <ul class="footer-widget-list">
                    @foreach($footerPages as $page)
                        <li><a href="{{ route('page.show', $page->slug) }}">{{ $page->title }}</a></li>
                    @endforeach
                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-widget-title">Shop By</h4>
                <ul class="footer-widget-list">
                    @foreach($footerCategories as $cat)
                        <li><a href="{{ route('category.show', $cat->slug) }}">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="footer-widget-title">My Account</h4>
                <ul class="footer-widget-list">
                    <li><a href="{{ route('account.index') }}">My Account</a></li>
                    <li><a href="{{ route('account.index') }}">Order History</a></li>
                    <li><a href="{{ route('track-order') }}">Track Order</a></li>
                    <li><a href="{{ route('cart.index') }}">My Cart</a></li>
                    <li><a href="{{ route('checkout') }}">Checkout</a></li>
                </ul>
            </div>
            <div class="footer-newsletter-col">
                <h4 class="footer-widget-title">Newsletter</h4>
                <p class="f-about-text" style="margin-top:0">Subscribe to get special offers, new arrivals and exclusive discounts.</p>
                <form action="{{ route('subscribe') }}" method="POST" class="footer-newsletter">
                    @csrf
                    <input type="email" name="email" placeholder="Your email address" required>
                    <button type="submit" aria-label="Subscribe"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
                <h4 class="footer-widget-title" style="margin-top:24px;margin-bottom:10px">We Accept</h4>
                <div class="payment-methods">
                    @if(setting('payment_banner'))
                        <img src="{{ upload_url(setting('payment_banner')) }}" alt="Payment methods">
                    @else
                        <span>Cash on Delivery</span><span style="color:#e2136e">bKash</span><span style="color:#f6921e">Nagad</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>{{ setting('copyright', '© ' . date('Y') . ' ' . setting('site_name', 'Grihosthali') . '. All rights reserved.') }}</p>
            <p class="bn">খাঁটি স্বাদ, সবার রান্নায়</p>
        </div>
    </div>
</footer>
