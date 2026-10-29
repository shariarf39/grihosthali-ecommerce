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
        'order_number', 'user_id', 'crm_customer_id', 'name', 'phone', 'email', 'address', 'shipping_area', 'note',
        'subtotal', 'shipping_charge', 'discount', 'coupon_code', 'total', 'payment_method',
        'payment_status', 'status', 'admin_note', 'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'shipping_charge' => 'float',
        'discount' => 'float',
        'total' => 'float',
        'delivered_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'crm_customer_id');
    }

    public function returns()
    {
        return $this->hasMany(OrderReturn::class)->latest();
    }

    /** Quantity of each order item already claimed by non-rejected returns, keyed by order_item_id. */
    public function returnedQuantities(): array
    {
        return OrderReturnItem::whereIn('order_return_id', $this->returns()->where('status', '!=', 'rejected')->select('id'))
            ->selectRaw('order_item_id, SUM(quantity) as qty')->groupBy('order_item_id')->pluck('qty', 'order_item_id')
            ->map(fn ($q) => (int) $q)->all();
    }

    /** Whether the customer may still request a return for this order. */
    public function isReturnable(): bool
    {
        if ($this->status !== 'delivered') {
            return false;
        }
        $days = (int) setting('return_days', 7);

        return $days > 0 && ($this->delivered_at ?? $this->updated_at)->gt(now()->subDays($days));
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
