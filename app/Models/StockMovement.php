<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    /** type => [label, badge colour] */
    public const TYPES = [
        'opening' => ['Opening Stock', 'secondary'],
        'purchase' => ['Purchase / Stock In', 'success'],
        'sale' => ['Sale', 'primary'],
        'cancel_restore' => ['Order Cancelled', 'info'],
        'reopen' => ['Order Reopened', 'warning'],
        'return' => ['Customer Return', 'purple'],
        'adjustment' => ['Adjustment', 'warning'],
        'damage' => ['Damage / Loss', 'danger'],
        'audit' => ['Stock Count (Audit)', 'secondary'],
    ];

    protected $fillable = ['product_id', 'user_id', 'type', 'quantity', 'stock_before', 'stock_after', 'unit_cost', 'reference', 'note'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type][0] ?? ucfirst($this->type);
    }

    public function getBadgeAttribute(): string
    {
        return self::TYPES[$this->type][1] ?? 'secondary';
    }
}
