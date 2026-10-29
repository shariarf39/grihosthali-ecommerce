<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'permissions', 'is_system'];

    protected $casts = ['permissions' => 'array', 'is_system' => 'boolean'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function isSuperAdmin(): bool
    {
        return in_array('*', $this->permissions ?? [], true);
    }

    public function allows(string $permission): bool
    {
        return $this->isSuperAdmin() || in_array($permission, $this->permissions ?? [], true);
    }
}
