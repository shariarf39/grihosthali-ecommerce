{{-- Stand-alone error page: no database or settings calls, so it still renders when the app is unhealthy. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('title') | Grihosthali</title>
    <link rel="icon" href="{{ asset('uploads/brand/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;
            background: radial-gradient(circle at 50% 30%, #fffdf7 0%, #f3ede0 75%); font-family: 'Poppins', sans-serif; color: #5b6475; }
        .box { width: 100%; max-width: 480px; text-align: center; background: #fff; border-radius: 18px; padding: 40px 30px;
            box-shadow: 0 24px 60px rgba(8, 70, 69, .12); border: 1px solid #efe7d4; }
        .logo { height: 64px; margin-bottom: 22px; }
        .code { font-size: 64px; font-weight: 600; line-height: 1; color: #084645; letter-spacing: -2px; }
        h1 { color: #1d2433; font-size: 21px; margin: 12px 0 8px; }
        p { margin: 0 0 26px; line-height: 1.6; }
        .actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        a.btn { display: inline-block; padding: 11px 22px; border-radius: 8px; text-decoration: none; font-weight: 500; }
        a.primary { background: #084645; color: #fff; }
        a.ghost { border: 1px solid #d9e2e1; color: #084645; }
    </style>
</head>
<body>
    <div class="box">
        <img class="logo" src="{{ asset('uploads/brand/logo.png') }}" alt="Grihosthali" onerror="this.style.display='none'">
        <div class="code">@yield('code')</div>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            <a href="{{ url('/') }}" class="btn primary">Go to homepage</a>
            @yield('extra')
        </div>
    </div>
</body>
</html>
