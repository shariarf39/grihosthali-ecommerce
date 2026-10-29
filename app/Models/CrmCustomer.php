<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmCustomer extends Model
{
    public const TAGS = ['VIP', 'Wholesale', 'Repeat Buyer', 'Corporate', 'Influencer', 'Fake Order Risk', 'Call Before Delivery'];

    protected $fillable = ['user_id', 'phone', 'name', 'email', 'address', 'source', 'tags', 'is_blocked', 'block_reason', 'notes', 'last_order_at'];

    protected $casts = [
        'tags' => 'array',
        'is_blocked' => 'boolean',
        'last_order_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function activities()
    {
        return $this->hasMany(CrmActivity::class)->latest();
    }

    public function returns()
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Create or refresh the CRM record for an order's customer and link the order to it. */
    public static function syncFromOrder(Order $order): self
    {
        $customer = self::firstOrNew(['phone' => normalize_phone($order->phone)]);
        $customer->fill([
            'name' => $order->name,
            'email' => $order->email ?: $customer->email,
            'address' => $order->address,
            'user_id' => $order->user_id ?: $customer->user_id,
        ]);
        $customer->last_order_at = max($customer->last_order_at, $order->created_at);
        $customer->save();

        if ($order->crm_customer_id !== $customer->id) {
            $order->forceFill(['crm_customer_id' => $customer->id])->saveQuietly();
        }

        return $customer;
    }

    /** Add order statistics (count, delivered, cancelled, spent) as attributes on a query. */
    public function scopeWithStats($query)
    {
        return $query->withCount([
            'orders',
            'orders as delivered_count' => fn ($q) => $q->where('status', 'delivered'),
            'orders as failed_count' => fn ($q) => $q->whereIn('status', ['cancelled', 'returned']),
        ])->withSum(['orders as total_spent' => fn ($q) => $q->where('status', 'delivered')], 'total');
    }

    /** Segment derived from order history. Requires withStats(). */
    public function getSegmentAttribute(): string
    {
        $spent = (float) ($this->total_spent ?? 0);
        $orders = (int) ($this->orders_count ?? 0);

        if ($this->is_blocked) {
            return 'Blocked';
        }
        if ($spent >= (float) setting('crm_vip_amount', 10000) || in_array('VIP', $this->tags ?? [])) {
            return 'VIP';
        }
        if ($this->last_order_at && $this->last_order_at->lt(now()->subDays((int) setting('crm_inactive_days', 90)))) {
            return 'At Risk';
        }
        if ($orders >= 2) {
            return 'Repeat';
        }

        return 'New';
    }

    /** Percentage of finished orders that were delivered — a quick fake-order indicator. */
    public function getSuccessRateAttribute(): ?int
    {
        $finished = (int) ($this->delivered_count ?? 0) + (int) ($this->failed_count ?? 0);

        return $finished ? (int) round($this->delivered_count / $finished * 100) : null;
    }

    public static function segmentBadge(string $segment): string
    {
        return ['VIP' => 'warning', 'Repeat' => 'success', 'New' => 'info', 'At Risk' => 'purple', 'Blocked' => 'danger'][$segment] ?? 'secondary';
    }
}
