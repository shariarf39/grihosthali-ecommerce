@extends('layouts.app')

@section('title', $title)

@section('content')
@include('partials.breadcrumb', ['items' => $category && $category->parent
    ? [$category->parent->name => route('category.show', $category->parent->slug), $title => null]
    : [$title => null]])

<div class="container">
    <div class="shop-layout">
        <aside class="shop-sidebar">
            <div class="sidebar-box">
                <h4>Categories</h4>
                <ul class="cat-list">
                    <li><a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'active' : '' }}">All Products</a></li>
                    @foreach($categories as $cat)
                        <li>
                            <a href="{{ route('category.show', $cat->slug) }}" class="{{ $category && $category->id === $cat->id ? 'active' : '' }}">
                                {{ $cat->name }} <span>{{ $cat->products_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="sidebar-box">
                <h4>Filter by Price</h4>
                <form method="GET">
                    @foreach(request()->except(['min', 'max', 'page']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <div class="price-filter">
                        <input type="number" name="min" class="form-control" placeholder="Min ৳" value="{{ request('min') }}" min="0">
                        <input type="number" name="max" class="form-control" placeholder="Max ৳" value="{{ request('max') }}" min="0">
                    </div>
                    <button class="btn btn-block">Apply Filter</button>
                </form>
            </div>
        </aside>

        <div>
            <div class="shop-toolbar">
                <div>
                    <h1>{{ $title }}</h1>
                    <span class="result-count">Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products</span>
                </div>
                <div style="display:flex;gap:10px;align-items:center">
                    <button type="button" class="btn btn-outline filter-toggle" data-toggle-filter><i class="fa-solid fa-filter"></i> Filter</button>
                    <form method="GET">
                        @foreach(request()->except(['sort', 'page']) as $k => $v)
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endforeach
                        <select name="sort" class="form-control" data-auto-submit aria-label="Sort products">
                            <option value="">Sort: Latest</option>
                            <option value="popular" @selected(request('sort') === 'popular')>Best Selling</option>
                            <option value="price_low" @selected(request('sort') === 'price_low')>Price: Low to High</option>
                            <option value="price_high" @selected(request('sort') === 'price_high')>Price: High to Low</option>
                            <option value="name" @selected(request('sort') === 'name')>Name: A–Z</option>
                        </select>
                    </form>
                </div>
            </div>

            @if($products->count())
                <div class="product-grid cols-4">
                    @foreach($products as $product)
                        @include('partials.product-card')
                    @endforeach
                </div>
                {{ $products->links() }}
            @else
                <div class="empty-state">
                    <i class="fa-solid fa-box-open"></i>
                    <h4>No products found</h4>
                    <p>Try a different search or browse all products.</p>
                    <a href="{{ route('shop') }}" class="btn">Browse All Products</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
