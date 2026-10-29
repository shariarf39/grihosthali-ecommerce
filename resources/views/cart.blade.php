@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
@include('partials.breadcrumb', ['items' => ['Shopping Cart' => null]])

<div class="container">
    @if(count($items))
        <div class="cart-page">
            <div class="checkout-card">
                <h3><i class="fa-solid fa-bag-shopping" style="color:var(--primary)"></i> Shopping Cart</h3>
                <div class="table-wrap">
                    <table class="table cart-table">
                        <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th></th></tr></thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>
                                        <div style="display:flex;gap:12px;align-items:center">
                                            <img src="{{ $item['product']->image_url }}" alt="">
                                            <a href="{{ route('product.show', $item['product']->slug) }}" style="color:var(--title);font-weight:500">{{ $item['product']->name }}</a>
                                        </div>
                                    </td>
                                    <td>{{ taka($item['product']->sell_price) }}</td>
                                    <td>
                                        <div class="mini-qty">
                                            <button type="button" data-cart-dec="{{ $item['product']->id }}" data-reload><i class="fa-solid fa-minus"></i></button>
                                            <span>{{ $item['qty'] }}</span>
                                            <button type="button" data-cart-inc="{{ $item['product']->id }}" data-reload><i class="fa-solid fa-plus"></i></button>
                                        </div>
                                    </td>
                                    <td><strong>{{ taka($item['total']) }}</strong></td>
                                    <td><button type="button" class="cs-remove" data-cart-remove="{{ $item['product']->id }}" data-reload aria-label="Remove"><i class="fa-regular fa-trash-can"></i></button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('shop') }}" class="btn btn-outline" style="margin-top:16px"><i class="fa-solid fa-arrow-left"></i> Continue Shopping</a>
            </div>
            <div class="checkout-card">
                <h3>Cart Summary</h3>
                <div class="os-line"><span>Subtotal</span><strong>{{ taka($subtotal) }}</strong></div>
                <div class="os-line"><span>Delivery</span><span>Calculated at checkout</span></div>
                <div class="os-line total"><span>Total</span><span>{{ taka($subtotal) }}</span></div>
                <a href="{{ route('checkout') }}" class="btn btn-block btn-lg" style="margin-top:16px">Proceed to Checkout <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    @else
        <div class="empty-state" style="margin:40px 0">
            <i class="fa-solid fa-basket-shopping"></i>
            <h4>Your cart is empty</h4>
            <p>Looks like you haven't added anything yet.</p>
            <a href="{{ route('shop') }}" class="btn">Start Shopping</a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Cart page shows server-rendered totals, so reload after any change.
    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-reload]')) setTimeout(() => location.reload(), 400);
    });
</script>
@endpush
