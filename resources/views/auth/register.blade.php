@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="container">
    <div class="auth-box">
        <h2>Create Account</h2>
        <p class="sub">Join {{ setting('site_name', 'Grihosthali') }} for a faster checkout</p>
        @if($errors->any())
            <div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="form-group"><label>Full Name</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
            <div class="form-group"><label>Mobile Number</label><input name="phone" class="form-control" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
            <div class="form-row">
                <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                <div class="form-group"><label>Confirm Password</label><input type="password" name="password_confirmation" class="form-control" required></div>
            </div>
            <button class="btn btn-block btn-lg">Create Account</button>
        </form>
        <p class="text-center" style="margin-top:18px">Already have an account? <a href="{{ route('login') }}" style="color:var(--primary);font-weight:500">Login</a></p>
    </div>
</div>
@endsection
