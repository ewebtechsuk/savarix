<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingEvent extends Model
{
    protected $fillable = [
        'session_id',
        'event_name',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];
}
