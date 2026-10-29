<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = ['title', 'subtitle', 'button_text', 'link', 'image', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** Links may be stored site-relative ("/category/honey") so they survive a move to another domain/folder. */
    public function getUrlAttribute(): string
    {
        if (! $this->link) {
            return route('shop');
        }

        return str_starts_with($this->link, '/') ? url($this->link) : $this->link;
    }

    public function getImageUrlAttribute(): string
    {
        return upload_url($this->image, 'images/placeholder.svg');
    }
}
