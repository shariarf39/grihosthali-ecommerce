<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'type', 'value', 'min_order', 'usage_limit', 'used', 'expires_at', 'is_active'];

    protected $casts = [
        'expires_at' => 'date',
        'is_active' => 'boolean',
        'value' => 'float',
        'min_order' => 'float',
    ];

    /** Returns an error message, or null when the coupon can be applied to this subtotal. */
    public function validationError(float $subtotal): ?string
    {
        if (! $this->is_active) {
            return 'This coupon is not active.';
        }
        if ($this->expires_at && $this->expires_at->endOfDay()->isPast()) {
            return 'This coupon has expired.';
        }
        if ($this->usage_limit !== null && $this->used >= $this->usage_limit) {
            return 'This coupon has reached its usage limit.';
        }
        if ($subtotal < $this->min_order) {
            return 'Minimum order of ' . taka($this->min_order) . ' is required for this coupon.';
        }

        return null;
    }

    public function discountFor(float $subtotal): float
    {
        $discount = $this->type === 'percent' ? $subtotal * $this->value / 100 : $this->value;

        return round(min($discount, $subtotal), 2);
    }
}
