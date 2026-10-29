@extends('layouts.app')

@section('content')

{{-- Hero slider --}}
@if($sliders->count())
<section class="hero">
    <div class="container">
        <div class="swiper hero-slider">
            <div class="swiper-wrapper">
                @foreach($sliders as $slide)
                    <div class="swiper-slide hero-slide">
                        <a href="{{ $slide->url }}">
                            <img src="{{ $slide->image_url }}" alt="{{ $slide->title ?: setting('site_name', 'Grihosthali') }}" @if(! $loop->first) loading="lazy" @endif>
                        </a>
                        @if($slide->title)
                            <div class="hero-caption">
                                @if($slide->button_text)<span class="eyebrow">{{ setting('site_tagline', '100% Pure & Natural') }}</span>@endif
                                <h2>{{ $slide->title }}</h2>
                                @if($slide->subtitle)<p>{{ $slide->subtitle }}</p>@endif
                                @if($slide->button_text)
                                    <a href="{{ $slide->url }}" class="btn btn-gold btn-lg">{{ $slide->button_text }} <i class="fa-solid fa-arrow-right"></i></a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
            <button class="slider-nav prev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="slider-nav next" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
        </div>

        <div class="features">
            <div class="feature-item"><div class="fi-icon"><i class="fa-solid fa-truck-fast"></i></div><div><h5>Fast Delivery</h5><p>All over Bangladesh</p></div></div>
            <div class="feature-item"><div class="fi-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div><div><h5>Cash on Delivery</h5><p>Pay when you receive</p></div></div>
            <div class="feature-item"><div class="fi-icon"><i class="fa-solid fa-leaf"></i></div><div><h5>100% Natural</h5><p>Pure & traditional</p></div></div>
            <div class="feature-item"><div class="fi-icon"><i class="fa-solid fa-headset"></i></div><div><h5>Support</h5><p>{{ setting('hotline', setting('phone', '24/7 help')) }}</p></div></div>
        </div>
    </div>
</section>
@endif

{{-- Featured categories --}}
@if($featuredCategories->count())
<section class="section">
    <div class="container">
        <div class="category-section-head">
            <span class="head-ornament"><i class="fa-solid fa-seedling"></i></span>
            <h3>Featured Categories</h3>
            <p>Get your desired product from featured category!</p>
        </div>
        <div class="category-slider-wrap">
            <div class="swiper category-slider">
                <div class="swiper-wrapper">
                    @foreach($featuredCategories as $cat)
                        <div class="swiper-slide">
                            <a href="{{ route('category.show', $cat->slug) }}" class="category-card">
                                <div class="category-img"><img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" loading="lazy"></div>
                                <div class="category-info"><p>{{ $cat->name }}</p></div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <button class="slider-nav prev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="slider-nav next" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </div>
</section>
@endif

{{-- Top selling --}}
@if($topSelling->count())
    @include('partials.product-rail', ['title' => 'Top Selling Products', 'icon' => 'fa-solid fa-fire', 'products' => $topSelling, 'link' => route('shop', ['sort' => 'popular'])])
@endif

{{-- Category sections --}}
@foreach($homeSections as $section)
    @include('partials.product-rail', ['title' => $section->name, 'products' => $section->homeProducts, 'link' => route('category.show', $section->slug)])

    @if($loop->index === 1)
        {{-- Brand story banner between sections --}}
        <section class="section">
            <div class="container">
                <div class="promo-banner">
                    <div class="pb-text">
                        <span class="eyebrow">Pure • Natural • Traditional</span>
                        <h3>Premium Desi Ghee</h3>
                        <p>খাঁটি স্বাদ, সবার রান্নায় — গ্রামের ঐতিহ্যবাহী পদ্ধতিতে তৈরি বিশুদ্ধ গাওয়া ঘি।</p>
                        <a href="{{ route('shop', ['q' => 'ghee']) }}" class="btn btn-gold btn-lg">Order Now <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div class="pb-img"><img src="{{ asset('uploads/sliders/ghee-hero.png') }}" alt="Grihosthali Premium Desi Ghee" loading="lazy"></div>
                </div>
            </div>
        </section>
    @endif
@endforeach

{{-- Brands --}}
@if($brands->count())
<section class="section">
    <div class="container">
        <div class="section-head">
            <h3>Our Brands</h3>
            <a href="{{ route('shop') }}" class="sec-btn">See all <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="swiper brand-slider">
            <div class="swiper-wrapper">
                @foreach($brands as $brand)
                    <div class="swiper-slide">
                        <a href="{{ route('brand.show', $brand->slug) }}" class="brand-card"><img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" loading="lazy"></a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- Just for you --}}
@if($justForYou->count())
<section class="section">
    <div class="container">
        <div class="section-head">
            <h3><i class="fa-solid fa-heart"></i> Just For You</h3>
        </div>
        <div class="product-grid">
            @foreach($justForYou as $product)
                @include('partials.product-card')
            @endforeach
        </div>
        <div class="text-center" style="margin-top:28px">
            <a href="{{ route('shop') }}" class="btn btn-outline btn-lg">View All Products <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>
@endif

{{-- Testimonials --}}
@if($testimonials->count())
<section class="section testimonial-section" style="padding:48px 0;margin-top:20px">
    <div class="container">
        <div class="category-section-head">
            <span class="head-ornament"><i class="fa-solid fa-quote-left"></i></span>
            <h3>What Our Customers Say</h3>
            <p>Thousands of happy families trust Grihosthali every day</p>
        </div>
        <div class="swiper testimonial-slider">
            <div class="swiper-wrapper">
                @foreach($testimonials as $t)
                    <div class="swiper-slide testimonial-card">
                        <div class="testimonial-rating">
                            @for($i = 1; $i <= 5; $i++)<i class="fa-{{ $i <= $t->rating ? 'solid' : 'regular' }} fa-star"></i>@endfor
                        </div>
                        <p class="testimonial-text">“{{ $t->message }}”</p>
                        <div class="testimonial-author">
                            @if($t->image_url)
                                <img src="{{ $t->image_url }}" class="avatar" alt="{{ $t->name }}">
                            @else
                                <span class="avatar">{{ mb_substr($t->name, 0, 1) }}</span>
                            @endif
                            <div><h5>{{ $t->name }}</h5><span>{{ $t->designation }}</span></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>
@endif

@endsection
