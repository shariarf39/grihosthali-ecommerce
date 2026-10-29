<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function cached(): array
    {
        return Cache::rememberForever('site_settings', function () {
            return Schema::hasTable('settings') ? self::pluck('value', 'key')->toArray() : [];
        });
    }

    public static function get(string $key, $default = null)
    {
        $value = self::cached()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            self::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('site_settings');
    }
}
