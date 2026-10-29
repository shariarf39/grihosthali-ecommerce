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

    </div>
</section>
@endif

@if(setting('home_show_features'))
<section class="{{ $sliders->count() ? '' : 'hero' }}">
    <div class="container">
        <div class="features">
            @for($i = 1; $i <= 4; $i++)
                @if(setting('feature' . $i . '_title'))
                    <div class="feature-item"><div class="fi-icon"><i class="{{ setting('feature' . $i . '_icon') }}"></i></div><div><h5>{{ setting('feature' . $i . '_title') }}</h5><p>{{ setting('feature' . $i . '_text') }}</p></div></div>
                @endif
            @endfor
        </div>
    </div>
</section>
@endif

{{-- Featured categories --}}
@if(setting('home_show_categories') && $featuredCategories->count())
<section class="section">
    <div class="container">
        <div class="category-section-head">
            <span class="head-ornament"><i class="fa-solid fa-seedling"></i></span>
            <h3>{{ setting('categories_title') }}</h3>
            <p>{{ setting('categories_subtitle') }}</p>
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
@if(setting('home_show_top_selling') && $topSelling->count())
    @include('partials.product-rail', ['title' => setting('top_selling_title'), 'icon' => 'fa-solid fa-fire', 'products' => $topSelling, 'link' => route('shop', ['sort' => 'popular'])])
@endif

@php $promoAfter = (int) setting('promo_after_section'); @endphp
@if($promoAfter === 0 || $homeSections->isEmpty())
    @include('partials.promo-banner')
@endif

{{-- Category sections --}}
@foreach($homeSections as $section)
    @include('partials.product-rail', ['title' => $section->name, 'products' => $section->homeProducts, 'link' => route('category.show', $section->slug)])

    @if($promoAfter > 0 && ($loop->iteration === $promoAfter || ($loop->last && $promoAfter > $loop->count)))
        @include('partials.promo-banner')
    @endif
@endforeach

{{-- Brands --}}
@if(setting('home_show_brands') && $brands->count())
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
@if(setting('home_show_just_for_you') && $justForYou->count())
<section class="section">
    <div class="container">
        <div class="section-head">
            <h3><i class="fa-solid fa-heart"></i> {{ setting('just_for_you_title') }}</h3>
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
@if(setting('home_show_testimonials') && $testimonials->count())
<section class="section testimonial-section" style="padding:48px 0;margin-top:20px">
    <div class="container">
        <div class="category-section-head">
            <span class="head-ornament"><i class="fa-solid fa-quote-left"></i></span>
            <h3>{{ setting('testimonials_title') }}</h3>
            <p>{{ setting('testimonials_subtitle') }}</p>
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
