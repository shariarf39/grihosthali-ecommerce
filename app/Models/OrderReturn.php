<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    public const STATUSES = [
        'requested' => ['Requested', 'warning'],
        'approved' => ['Approved', 'info'],
        'received' => ['Product Received', 'primary'],
        'refunded' => ['Refunded', 'success'],
        'replaced' => ['Replacement Sent', 'success'],
        'rejected' => ['Rejected', 'danger'],
    ];

    public const REASONS = [
        'Damaged product received',
        'Wrong product delivered',
        'Quality not as expected',
        'Expired / near expiry',
        'Missing items',
        'Changed my mind',
        'Other',
    ];

    public const RESOLUTIONS = ['refund' => 'Refund', 'replacement' => 'Replacement'];

    public const REFUND_METHODS = ['cash' => 'Cash', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'bank' => 'Bank Transfer', 'store_credit' => 'Store Credit'];

    protected $fillable = [
        'return_number', 'order_id', 'crm_customer_id', 'name', 'phone', 'reason', 'resolution', 'customer_note',
        'status', 'refund_amount', 'refund_method', 'refund_reference', 'admin_note', 'restocked', 'created_by',
        'received_at', 'refunded_at',
    ];

    protected $casts = [
        'refund_amount' => 'float',
        'restocked' => 'boolean',
        'received_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(OrderReturnItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'crm_customer_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0] ?? ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUSES[$this->status][1] ?? 'secondary';
    }

    public function getItemsTotalAttribute(): float
    {
        return (float) $this->items->sum('total');
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, ['refunded', 'replaced', 'rejected']);
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'RT' . now()->format('ymd') . random_int(1000, 9999);
        } while (self::where('return_number', $number)->exists());

        return $number;
    }
}
