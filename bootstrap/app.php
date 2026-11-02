<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
 * Deployment safety. `php artisan config:cache` writes bootstrap/cache/config.php with absolute paths.
 * If the project is copied elsewhere (e.g. uploaded from XAMPP on Windows to a Linux host), that file
 * points at the old machine and silently overrides the server's .env (wrong DB, debug on, paths like
 * C:\xampp\...). Detect a cache built for another location and drop it, so the server's .env is used.
 */
(function (string $base): void {
    $norm = function (string $path): string {
        $path = rtrim(str_replace('\\', '/', $path), '/');

        return PHP_OS_FAMILY === 'Windows' ? strtolower($path) : $path;
    };

    $cache = $base.'/bootstrap/cache/config.php';
    if (is_file($cache)) {
        $config = @include $cache;
        $compiled = is_array($config) ? (string) ($config['view']['compiled'] ?? '') : '';
        if ($compiled === '' || ! str_starts_with($norm($compiled), $norm($base).'/')) {
            @unlink($cache);
        }
    }

    // On Linux, a stale Windows cache creates folders literally named "C:\xampp\...\storage\logs".
    // Remove any such leftovers from the project root and /public.
    if (PHP_OS_FAMILY !== 'Windows') {
        foreach ([$base, $base.'/public'] as $dir) {
            foreach (@scandir($dir) ?: [] as $entry) {
                $path = $dir.'/'.$entry;
                if (! preg_match('/^[A-Za-z]:\\\\/', $entry) || ! is_dir($path)) {
                    continue;
                }
                $items = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($items as $item) {
                    $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
                }
                @rmdir($path);
            }
        }
    }
})(dirname(__DIR__));

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'admin.log' => \App\Http\Middleware\LogAdminActivity::class,
        ]);
        $middleware->validateCsrfTokens(except: ['webhooks/*']);
        $middleware->redirectGuestsTo(fn ($request) => $request->is('admin*') ? route('admin.login') : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
