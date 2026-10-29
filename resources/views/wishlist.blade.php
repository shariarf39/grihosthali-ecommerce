@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
@include('partials.breadcrumb', ['items' => ['Wishlist' => null]])

<div class="container" style="padding:28px 16px">
    <div class="shop-toolbar">
        <div>
            <h1><i class="fa-regular fa-heart" style="color:var(--danger)"></i> My Wishlist</h1>
            <span class="result-count">{{ $products->count() }} saved {{ \Illuminate\Support\Str::plural('product', $products->count()) }}</span>
        </div>
        @guest
            <span class="result-count"><a href="{{ route('login') }}" style="color:var(--primary);font-weight:500">Sign in</a> to keep your wishlist on every device.</span>
        @endguest
    </div>

    @if($products->count())
        <div class="product-grid" data-wishlist-page>
            @foreach($products as $product)
                @include('partials.product-card')
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fa-regular fa-heart"></i>
            <h4>Your wishlist is empty</h4>
            <p>Tap the <i class="fa-regular fa-heart"></i> on any product to save it for later.</p>
            <a href="{{ route('shop') }}" class="btn">Browse Products</a>
        </div>
    @endif
</div>
@endsection
