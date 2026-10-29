@extends('emails.layout')

@section('body')
    <h2 style="margin:0 0 6px;color:#222;">Welcome, {{ $user->name }}! 🌿</h2>
    <p>Thank you for joining {{ setting('site_name') }}. You can now check out faster, see your order history and request returns from your account.</p>
    <p style="text-align:center;margin:24px 0 6px;">
        <a href="{{ route('shop') }}" style="background:{{ setting('primary_color', '#1f4d36') }};color:#fff;padding:12px 26px;border-radius:6px;text-decoration:none;font-weight:600;display:inline-block;">Start shopping</a>
    </p>
@endsection
