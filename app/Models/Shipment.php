<?php

namespace App\Models;

use App\Services\Couriers\CourierManager;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'order_id', 'courier', 'consignment_id', 'tracking_code', 'status', 'cod_amount', 'delivery_fee',
        'request', 'response', 'is_active', 'last_synced_at', 'user_id',
    ];

    protected $casts = [
        'request' => 'array',
        'response' => 'array',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'cod_amount' => 'float',
        'delivery_fee' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCourierLabelAttribute(): string
    {
        return CourierManager::LABELS[$this->courier] ?? ucfirst($this->courier);
    }

    public function getStatusLabelAttribute(): string
    {
        return ucwords(str_replace(['_', '-', '.'], ' ', $this->status));
    }

    public function getTrackingUrlAttribute(): ?string
    {
        return app(CourierManager::class)->driver($this->courier)->trackingUrl($this);
    }

    public function getStatusBadgeAttribute(): string
    {
        $s = strtolower($this->status);

        return match (true) {
            str_contains($s, 'deliver') && ! str_contains($s, 'fail') && ! str_contains($s, 'partial') => 'success',
            str_contains($s, 'cancel') || str_contains($s, 'return') || str_contains($s, 'fail') => 'danger',
            str_contains($s, 'hold') || str_contains($s, 'review') || str_contains($s, 'pending') => 'warning',
            default => 'info',
        };
    }
}
