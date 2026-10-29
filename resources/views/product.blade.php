@extends('layouts.app')

@section('title', $product->meta_title ?: $product->name)
@section('meta_description', $product->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($product->short_description), 155))
@section('og_image', $product->image_url)

@section('content')
@include('partials.breadcrumb', ['items' => array_filter([
    $product->category?->name => $product->category ? route('category.show', $product->category->slug) : null,
    $product->name => null,
], fn ($v, $k) => $k !== '', ARRAY_FILTER_USE_BOTH)])

<div class="container">
    <div class="product-single">
        <div class="pd-gallery">
            <div class="pd-main-img">
                <div class="swiper pd-main-swiper">
                    <div class="swiper-wrapper">
                        @foreach($product->gallery_urls as $img)
                            <div class="swiper-slide"><img src="{{ $img }}" alt="{{ $product->name }}"></div>
                        @endforeach
                    </div>
                    @if(count($product->gallery_urls) > 1)
                        <button class="slider-nav prev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="slider-nav next" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
                    @endif
                </div>
                @if($product->flag)<span class="flag-name">{{ $product->flag }}</span>@endif
            </div>
            @if(count($product->gallery_urls) > 1)
                <div class="swiper pd-thumbs">
                    <div class="swiper-wrapper">
                        @foreach($product->gallery_urls as $img)
                            <div class="swiper-slide"><img src="{{ $img }}" alt=""></div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="pd-info">
            <h1>{{ $product->name }}</h1>
            <div class="pd-meta">
                <span class="stars">
                    @for($i = 1; $i <= 5; $i++)<i class="fa-{{ $i <= round($avgRating) ? 'solid' : 'regular' }} fa-star"></i>@endfor
                    <span style="color:var(--muted)">({{ $reviews->count() }} reviews)</span>
                </span>
                @if($product->in_stock)
                    <span class="in-stock"><i class="fa-solid fa-circle-check"></i> In Stock</span>
                @else
                    <span class="out-stock"><i class="fa-solid fa-circle-xmark"></i> Out of Stock</span>
                @endif
                @if($product->sku)<span>SKU: {{ $product->sku }}</span>@endif
            </div>

            <div class="pd-price">
                <ins>{{ taka($product->sell_price) }}</ins>
                @if($product->hasDiscount())
                    <del>{{ taka($product->price) }}</del>
                    <span class="save-label">Save {{ $product->save_percent }}%</span>
                @endif
            </div>

            @if($product->short_description)
                <div class="pd-short">{!! nl2br(e($product->short_description)) !!}</div>
            @endif

            @if($product->in_stock)
                <div class="pd-qty-row">
                    <label for="pd-qty">Quantity:</label>
                    <div class="qty-input">
                        <button type="button" data-qty-step="-1" aria-label="Decrease"><i class="fa-solid fa-minus"></i></button>
                        <input type="number" id="pd-qty" value="1" min="1" max="{{ $product->stock }}">
                        <button type="button" data-qty-step="1" aria-label="Increase"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    @if($product->unit)<span style="color:var(--muted)">{{ $product->unit }}</span>@endif
                </div>

                <div class="pd-actions">
                    <button type="button" class="btn btn-outline" data-add-with-qty="{{ $product->id }}"><i class="fa-solid fa-cart-plus"></i> Add to Cart</button>
                    <form action="{{ route('cart.buy-now') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="qty" value="1" class="pd-qty-mirror">
                        <button class="btn btn-block shake-btn" style="height:50px"><i class="fa-solid fa-bolt"></i> Buy Now</button>
                    </form>
                </div>
            @endif

            <div class="pd-actions-2">
                @if(setting('whatsapp'))
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}?text={{ urlencode('I want to order: ' . $product->name . ' (' . route('product.show', $product->slug) . ')') }}" target="_blank" rel="noopener" class="btn btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> Order on WhatsApp</a>
                @endif
                @if(setting('hotline', setting('phone')))
                    <a href="tel:{{ setting('hotline', setting('phone')) }}" class="btn btn-dark"><i class="fa-solid fa-phone-volume"></i> Call for order</a>
                @endif
            </div>

            <div class="pd-extra">
                <div><i class="fa-solid fa-truck-fast"></i> Inside Dhaka delivery: {{ taka(setting('shipping_inside', 70)) }} (1–2 days)</div>
                <div><i class="fa-solid fa-truck"></i> Outside Dhaka delivery: {{ taka(setting('shipping_outside', 130)) }} (2–4 days)</div>
                <div><i class="fa-solid fa-hand-holding-dollar"></i> Cash on delivery available</div>
            </div>

            <div class="pd-cat-line">
                @if($product->category)<div>Category: <a href="{{ route('category.show', $product->category->slug) }}">{{ $product->category->name }}</a></div>@endif
                @if($product->brand)<div>Brand: <a href="{{ route('brand.show', $product->brand->slug) }}">{{ $product->brand->name }}</a></div>@endif
            </div>
        </div>
    </div>

    <div class="product-tabs">
        <div class="tab-nav">
            <button class="active" data-tab="tab-desc">Description</button>
            <button data-tab="tab-reviews">Customer Reviews ({{ $reviews->count() }})</button>
        </div>
        <div class="tab-pane active" id="tab-desc">
            <div class="rich-content">
                {!! $product->description ?: '<p>' . e($product->short_description) . '</p>' !!}
            </div>
        </div>
        <div class="tab-pane" id="tab-reviews">
            <div class="review-summary">
                <div class="review-score">
                    <strong>{{ number_format($avgRating, 1) }}</strong>
                    <div class="stars">@for($i = 1; $i <= 5; $i++)<i class="fa-{{ $i <= round($avgRating) ? 'solid' : 'regular' }} fa-star"></i>@endfor</div>
                    <span>Average Rating ({{ $reviews->count() }} reviews)</span>
                </div>
                <div>
                    @forelse($reviews as $review)
                        <div class="review-item">
                            <div class="ri-head">
                                <div><strong>{{ $review->name }}</strong>
                                    <div class="stars">@for($i = 1; $i <= 5; $i++)<i class="fa-{{ $i <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>@endfor</div>
                                </div>
                                <small>{{ $review->created_at->format('d M, Y') }}</small>
                            </div>
                            <p>{{ $review->comment }}</p>
                        </div>
                    @empty
                        <p>No reviews yet. Be the first to review this product!</p>
                    @endforelse
                </div>
            </div>

            <h4>Submit Your Review</h4>
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form action="{{ route('product.review', $product) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Your Rating <span class="req">*</span></label>
                    <div class="star-input">
                        @for($i = 5; $i >= 1; $i--)
                            <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" @checked(old('rating', 5) == $i)>
                            <label for="star{{ $i }}"><i class="fa-solid fa-star"></i></label>
                        @endfor
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Name <span class="req">*</span></label><input name="name" class="form-control" value="{{ old('name', auth()->user()?->name) }}" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()?->email) }}"></div>
                </div>
                <div class="form-group"><label>Your Review <span class="req">*</span></label><textarea name="comment" rows="4" class="form-control" required>{{ old('comment') }}</textarea></div>
                <button class="btn">Submit Review</button>
            </form>
        </div>
    </div>
</div>

@if($related->count())
    @include('partials.product-rail', ['title' => 'Related Products', 'products' => $related])
@endif
@if($moreProducts->count())
    @include('partials.product-rail', ['title' => 'More Products', 'products' => $moreProducts, 'link' => route('shop')])
@endif
@endsection
