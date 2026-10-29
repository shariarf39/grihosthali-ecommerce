@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
@include('partials.breadcrumb', ['items' => ['Contact Us' => null]])

<div class="container">
    <div class="contact-grid">
        <div>
            <h2>Get in Touch</h2>
            <p>Have a question about our products or your order? We'd love to hear from you.</p>
            @if(setting('address'))<div class="contact-info-item"><i class="fa-solid fa-location-dot"></i><div><h5>Address</h5><p>{{ setting('address') }}</p></div></div>@endif
            @if(setting('phone'))<div class="contact-info-item"><i class="fa-solid fa-phone"></i><div><h5>Phone</h5><p><a href="tel:{{ setting('phone') }}">{{ setting('phone') }}</a></p></div></div>@endif
            @if(setting('email'))<div class="contact-info-item"><i class="fa-regular fa-envelope"></i><div><h5>Email</h5><p><a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a></p></div></div>@endif
            @if(setting('whatsapp'))<div class="contact-info-item"><i class="fa-brands fa-whatsapp"></i><div><h5>WhatsApp</h5><p><a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}" target="_blank" rel="noopener">{{ setting('whatsapp') }}</a></p></div></div>@endif
        </div>
        <div class="checkout-card">
            <h3>Send us a message</h3>
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('contact.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group"><label>Name <span class="req">*</span></label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
                    <div class="form-group"><label>Phone</label><input name="phone" class="form-control" value="{{ old('phone') }}"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}"></div>
                    <div class="form-group"><label>Subject</label><input name="subject" class="form-control" value="{{ old('subject') }}"></div>
                </div>
                <div class="form-group"><label>Message <span class="req">*</span></label><textarea name="message" rows="5" class="form-control" required>{{ old('message') }}</textarea></div>
                <button class="btn btn-lg">Send Message <i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</div>
@endsection
