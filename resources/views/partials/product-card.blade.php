{{-- Product card: image + flag + save badge, name, price, "Add To Cart" that turns into a −/+ quantity box. --}}
<div class="product-card">
    <figure class="product-media">
        <a href="{{ route('product.show', $product->slug) }}">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
        </a>
        @if($product->flag)
            <span class="flag-name">{{ $product->flag }}</span>
        @endif
        @if($product->hasDiscount())
            <span class="save-label">Save {{ $product->save_percent }}%</span>
        @endif
        @unless($product->in_stock)
            <span class="stock-out-label">Out of Stock</span>
        @endunless
        <button type="button" class="wishlist-btn" data-wishlist="{{ $product->id }}" aria-label="Add {{ $product->name }} to wishlist">
            <i class="fa-regular fa-heart"></i>
        </button>
    </figure>
    <div class="product-details">
        <h4 class="product-name">
            <a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a>
        </h4>
        <div class="product-price">
            <ins class="new-price">{{ taka($product->sell_price) }}</ins>
            @if($product->hasDiscount())
                <del class="old-price">{{ taka($product->price) }}</del>
            @endif
        </div>
    </div>
    <div class="product-card-action" data-id="{{ $product->id }}">
        <button type="button" class="open-quantity-btn" @disabled(! $product->in_stock)>
            <i class="fa-solid fa-cart-shopping"></i> {{ $product->in_stock ? 'Add To Cart' : 'Stock Out' }}
        </button>
        <div class="quantity-box hidden">
            <button type="button" class="decrease-btn" aria-label="Decrease"><i class="fa-solid fa-minus"></i></button>
            <span class="quantity">0</span>
            <button type="button" class="increase-btn" aria-label="Increase"><i class="fa-solid fa-plus"></i></button>
        </div>
    </div>
</div>
