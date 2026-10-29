@if(setting('home_show_promo') && setting('promo_title'))
    @php
        $promoLink = setting('promo_link') ?: route('shop');
        $promoLink = str_starts_with($promoLink, '/') ? url($promoLink) : $promoLink;
    @endphp
    <section class="section">
        <div class="container">
            <div class="promo-banner">
                <div class="pb-text">
                    @if(setting('promo_eyebrow'))<span class="eyebrow">{{ setting('promo_eyebrow') }}</span>@endif
                    <h3>{{ setting('promo_title') }}</h3>
                    @if(setting('promo_text'))<p>{{ setting('promo_text') }}</p>@endif
                    @if(setting('promo_button'))
                        <a href="{{ $promoLink }}" class="btn btn-gold btn-lg">{{ setting('promo_button') }} <i class="fa-solid fa-arrow-right"></i></a>
                    @endif
                </div>
                <div class="pb-img">
                    <img src="{{ setting('promo_image') ? upload_url(setting('promo_image')) : asset('uploads/sliders/ghee-hero.png') }}" alt="{{ setting('promo_title') }}" loading="lazy">
                </div>
            </div>
        </div>
    </section>
@endif
