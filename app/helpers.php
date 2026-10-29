<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('taka')) {
    /** Format an amount as Bangladeshi Taka, e.g. ৳1,445 */
    function taka($amount): string
    {
        $amount = (float) $amount;
        $decimals = floor($amount) == $amount ? 0 : 2;

        return '৳' . number_format($amount, $decimals);
    }
}

if (! function_exists('upload_url')) {
    /** Public URL for a file stored under public/uploads (or an absolute URL as-is). */
    function upload_url(?string $path, ?string $fallback = null): string
    {
        if (! $path) {
            return $fallback ? asset($fallback) : '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('uploads/' . ltrim($path, '/'));
    }
}

if (! function_exists('upload_file')) {
    /** Move an uploaded file to public/uploads/{folder} and return the relative path. */
    function upload_file(\Illuminate\Http\UploadedFile $file, string $folder, ?string $old = null): string
    {
        delete_upload($old);
        $name = now()->format('YmdHis') . '_' . \Illuminate\Support\Str::random(8) . '.' . strtolower($file->getClientOriginalExtension());
        $file->move(public_path('uploads/' . $folder), $name);

        return $folder . '/' . $name;
    }
}

if (! function_exists('delete_upload')) {
    function delete_upload(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http') && is_file(public_path('uploads/' . $path))) {
            @unlink(public_path('uploads/' . $path));
        }
    }
}

if (! function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        return [
            'pending' => 'warning',
            'confirmed' => 'info',
            'processing' => 'primary',
            'shipped' => 'purple',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'returned' => 'secondary',
        ][$status] ?? 'secondary';
    }
}
