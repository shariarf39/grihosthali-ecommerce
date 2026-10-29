@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container">
    <div class="auth-box">
        <h2>Welcome Back</h2>
        <p class="sub">Login to track orders and checkout faster</p>
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group"><label>Email or Phone</label><input name="login" class="form-control" value="{{ old('login') }}" required autofocus></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
            <div class="form-group"><label style="display:flex;gap:8px;align-items:center;font-weight:400"><input type="checkbox" name="remember"> Remember me</label></div>
            <button class="btn btn-block btn-lg">Login</button>
        </form>
        <p class="text-center" style="margin-top:18px">Don't have an account? <a href="{{ route('register') }}" style="color:var(--primary);font-weight:500">Register</a></p>
    </div>
</div>
@endsection
