{{-- A titled horizontal product slider. Expects $title, $products, optional $link and $icon. --}}
<section class="section">
    <div class="container">
        <div class="section-head">
            <h3>@isset($icon)<i class="{{ $icon }}"></i>@endisset {{ $title }}</h3>
            <div class="sec-actions">
                @isset($link)
                    <a href="{{ $link }}" class="sec-btn">View all items <i class="fa-solid fa-arrow-right"></i></a>
                @endisset
                <div class="rail-nav">
                    <button type="button" class="rail-prev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
                    <button type="button" class="rail-next" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
        <div class="swiper product-rail">
            <div class="swiper-wrapper">
                @foreach($products as $product)
                    <div class="swiper-slide">@include('partials.product-card')</div>
                @endforeach
            </div>
        </div>
    </div>
</section>
