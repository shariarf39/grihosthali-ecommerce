<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    protected $fillable = ['event', 'to', 'subject', 'status', 'error'];
}
