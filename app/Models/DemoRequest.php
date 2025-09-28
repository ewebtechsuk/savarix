<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoRequest extends Model
{
    protected $fillable = [
        'lead_id',
        'contact_id',
        'scheduled_at',
        'timezone',
        'status',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
