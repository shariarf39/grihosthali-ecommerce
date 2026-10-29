<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    protected $fillable = ['user_id', 'action', 'description', 'url', 'ip'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?string $description = null): void
    {
        self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'url' => request()->path(),
            'ip' => request()->ip(),
        ]);
    }
}
