<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned',
    ];

    protected $fillable = [
        'order_number', 'user_id', 'name', 'phone', 'email', 'address', 'shipping_area', 'note',
        'subtotal', 'shipping_charge', 'discount', 'coupon_code', 'total', 'payment_method',
        'payment_status', 'status', 'admin_note',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'shipping_charge' => 'float',
        'discount' => 'float',
        'total' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'GH' . now()->format('ymd') . random_int(1000, 9999);
        } while (self::where('order_number', $number)->exists());

        return $number;
    }
}
