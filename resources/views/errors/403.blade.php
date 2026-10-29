<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access denied | {{ setting('site_name', 'Grihosthali') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f4f6f8; font-family: 'Poppins', sans-serif; color: #5b6475; padding: 16px; box-sizing: border-box; }
        .box { background: #fff; border: 1px solid #e7e9ee; border-radius: 16px; padding: 40px 32px; max-width: 440px; width: 100%; text-align: center; box-shadow: 0 18px 40px rgba(16,24,40,.08); }
        .icon { width: 72px; height: 72px; border-radius: 50%; background: #fdeaea; color: #dc3545; display: flex; align-items: center; justify-content: center; font-size: 30px; margin: 0 auto 18px; }
        h1 { color: #1d2433; font-size: 22px; margin: 0 0 8px; }
        p { margin: 0 0 24px; line-height: 1.6; }
        a { display: inline-flex; gap: 8px; align-items: center; background: #1f4d36; color: #fff; padding: 11px 22px; border-radius: 8px; text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon"><i class="fa-solid fa-lock"></i></div>
        <h1>You don't have access to this page</h1>
        <p>{{ $exception->getMessage() && $exception->getMessage() !== 'This action is unauthorized.' ? $exception->getMessage() : 'Your role does not include this section. Ask a Super Admin if you need access.' }}</p>
        <a href="{{ auth()->user()?->is_admin ? route('admin.dashboard') : route('home') }}"><i class="fa-solid fa-arrow-left"></i> Go back</a>
    </div>
</body>
</html>
