<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    protected $fillable = ['image', 'caption', 'link', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function getImageUrlAttribute(): string
    {
        return upload_url($this->image, 'images/placeholder.svg');
    }

    /** Links may be site-relative ("/category/honey") so they survive a domain/folder move. */
    public function getUrlAttribute(): ?string
    {
        if (! $this->link) {
            return null;
        }

        return str_starts_with($this->link, '/') ? url($this->link) : $this->link;
    }
}
