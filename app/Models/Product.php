<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'sku', 'unit', 'short_description', 'description',
        'price', 'discount_price', 'cost_price', 'stock', 'low_stock_alert', 'image', 'gallery', 'flag', 'is_top_selling', 'is_active',
        'sold_count', 'views', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'gallery' => 'array',
        'is_top_selling' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'float',
        'discount_price' => 'float',
        'cost_price' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->latest();
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'low_stock_alert');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->low_stock_alert;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && $this->discount_price > 0 && $this->discount_price < $this->price;
    }

    /** Price the customer actually pays. */
    public function getSellPriceAttribute(): float
    {
        return $this->hasDiscount() ? $this->discount_price : $this->price;
    }

    public function getSavePercentAttribute(): int
    {
        return $this->hasDiscount() ? (int) round(($this->price - $this->discount_price) / $this->price * 100) : 0;
    }

    public function getImageUrlAttribute(): string
    {
        return upload_url($this->image, 'images/placeholder.svg');
    }

    public function getGalleryUrlsAttribute(): array
    {
        $images = array_filter(array_merge([$this->image], $this->gallery ?? []));

        return array_values(array_map(fn ($img) => upload_url($img, 'images/placeholder.svg'), $images));
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock > 0;
    }
}
