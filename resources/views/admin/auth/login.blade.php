<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | {{ setting('site_name', 'Grihosthali') }}</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Bengali:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<div class="login-page">
    <div class="login-art">
        <h2>গৃহস্থালী</h2>
        <p>Pure • Natural • Traditional — manage your store, orders and products from one place.</p>
    </div>
    <div class="login-form">
        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:26px">
                <span style="width:44px;height:44px;border-radius:12px;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px"><i class="fa-solid fa-seedling"></i></span>
                <div><h2 style="margin:0">Admin Login</h2><small style="color:var(--muted)">Sign in to your dashboard</small></div>
            </div>
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
            <label class="check"><input type="checkbox" name="remember"> Remember me</label>
            <button class="btn" style="width:100%;justify-content:center;height:46px">Login <i class="fa-solid fa-arrow-right"></i></button>
            <p style="text-align:center;margin-top:20px"><a href="{{ route('home') }}"><i class="fa-solid fa-arrow-left"></i> Back to store</a></p>
        </form>
    </div>
</div>
</body>
</html>
