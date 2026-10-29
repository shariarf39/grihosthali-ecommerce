@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
@include('partials.breadcrumb', ['items' => ['Checkout' => null]])

<div class="container">
    <div class="checkout-layout">
        <div class="checkout-card">
            <div class="checkout-notice">
                <i class="fa-solid fa-circle-info"></i>
                {{ setting('checkout_notice') }}
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form"
                  data-subtotal="{{ $subtotal }}" data-discount="{{ $discount }}" data-free-min="{{ $freeShippingMin }}">
                @csrf
                <h3><span class="step">1</span> Billing & Shipping Details</h3>
                <div class="form-group">
                    <label for="name">Full Name <span class="req">*</span></label>
                    <input id="name" name="name" class="form-control" value="{{ old('name', $user?->name) }}" placeholder="আপনার নাম লিখুন" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Mobile Number <span class="req">*</span></label>
                        <input id="phone" name="phone" class="form-control" value="{{ old('phone', $user?->phone) }}" placeholder="01XXXXXXXXX" required inputmode="tel">
                    </div>
                    <div class="form-group">
                        <label for="email">Email (optional)</label>
                        <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $user?->email) }}" placeholder="you@example.com">
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Full Address <span class="req">*</span></label>
                    <textarea id="address" name="address" rows="3" class="form-control" placeholder="বাসা নং, রোড, এলাকা, থানা, জেলা" required>{{ old('address', $user?->address) }}</textarea>
                </div>

                <h3 style="margin-top:24px"><span class="step">2</span> Delivery Area</h3>
                <div class="radio-cards">
                    @foreach($rates as $key => $rate)
                        <div class="radio-card">
                            <input type="radio" name="shipping_area" id="area-{{ $key }}" value="{{ $key }}" data-charge="{{ $rate['charge'] }}" @checked(old('shipping_area', 'inside') === $key)>
                            <label for="area-{{ $key }}"><span>{{ $rate['label'] }}</span><strong>{{ taka($rate['charge']) }}</strong></label>
                        </div>
                    @endforeach
                </div>
                @if($freeShippingMin > 0)
                    <p style="margin:10px 0 0;font-size:13px"><i class="fa-solid fa-gift" style="color:var(--secondary)"></i> Free delivery on orders over {{ taka($freeShippingMin) }}</p>
                @endif

                <h3 style="margin-top:24px"><span class="step">3</span> Payment Method</h3>
                <div class="pay-method">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <div><strong style="color:var(--title)">Cash on Delivery</strong><div style="font-size:13px">পণ্য হাতে পেয়ে মূল্য পরিশোধ করুন</div></div>
                    <i class="fa-solid fa-circle-check" style="margin-left:auto;font-size:18px"></i>
                </div>
                <input type="hidden" name="payment_method" value="cod">

                <div class="form-group" style="margin-top:20px">
                    <label for="note">Order Note (optional)</label>
                    <textarea id="note" name="note" rows="2" class="form-control" placeholder="Any special instruction for delivery">{{ old('note') }}</textarea>
                </div>
            </form>
        </div>

        <div class="checkout-card order-summary">
            <h3><i class="fa-solid fa-receipt" style="color:var(--primary)"></i> Order Summary</h3>
            @foreach($items as $item)
                <div class="os-item">
                    <img src="{{ $item['product']->image_url }}" alt="">
                    <div class="os-name">{{ $item['product']->name }}<small>{{ taka($item['product']->sell_price) }} × {{ $item['qty'] }}</small></div>
                    <div class="os-total">{{ taka($item['total']) }}</div>
                </div>
            @endforeach

            @if($coupon)
                <div class="applied-coupon">
                    <span><i class="fa-solid fa-tag" style="color:var(--primary)"></i> <strong>{{ $coupon->code }}</strong> applied</span>
                    <form action="{{ route('checkout.coupon.remove') }}" method="POST">@csrf<button>Remove</button></form>
                </div>
            @else
                <form action="{{ route('checkout.coupon') }}" method="POST" class="coupon-form">
                    @csrf
                    <input name="coupon_code" class="form-control" placeholder="Coupon code" value="{{ old('coupon_code') }}">
                    <button class="btn btn-dark">Apply</button>
                </form>
            @endif

            <div class="os-lines">
                <div class="os-line"><span>Subtotal</span><span>{{ taka($subtotal) }}</span></div>
                <div class="os-line"><span>Delivery Charge</span><span id="os-shipping">—</span></div>
                @if($discount > 0)
                    <div class="os-line" style="color:var(--success)"><span>Discount</span><span>−{{ taka($discount) }}</span></div>
                @endif
                <div class="os-line total"><span>Total</span><span id="os-total">{{ taka($subtotal - $discount) }}</span></div>
            </div>

            <button type="submit" form="checkout-form" id="place-order-btn" class="btn btn-block btn-lg shake-btn" style="margin-top:18px;height:54px;font-size:16px">
                <i class="fa-solid fa-lock"></i> <span class="bn">{{ setting('checkout_button') }}</span>
            </button>
            <p style="text-align:center;font-size:12.5px;margin:12px 0 0;color:var(--muted)"><i class="fa-solid fa-shield-halved"></i> Your information is safe with us</p>
        </div>
    </div>
</div>
@endsection
