<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmActivity extends Model
{
    public const TYPES = [
        'note' => ['Note', 'fa-regular fa-note-sticky'],
        'call' => ['Phone Call', 'fa-solid fa-phone'],
        'whatsapp' => ['WhatsApp', 'fa-brands fa-whatsapp'],
        'sms' => ['SMS', 'fa-regular fa-message'],
        'email' => ['Email', 'fa-regular fa-envelope'],
        'follow_up' => ['Follow-up Task', 'fa-regular fa-bell'],
        'system' => ['System', 'fa-solid fa-gear'],
    ];

    protected $fillable = ['crm_customer_id', 'user_id', 'type', 'message', 'due_at', 'completed_at'];

    protected $casts = ['due_at' => 'datetime', 'completed_at' => 'datetime'];

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'crm_customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePendingFollowUps($query)
    {
        return $query->where('type', 'follow_up')->whereNull('completed_at');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type][0] ?? ucfirst($this->type);
    }

    public function getIconAttribute(): string
    {
        return self::TYPES[$this->type][1] ?? 'fa-solid fa-circle';
    }
}
