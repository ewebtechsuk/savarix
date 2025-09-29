<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    protected $fillable = [
        'tenant_id',
        'status',
        'provider',
        'provider_reference',
        'provider_session_url',
        'session_metadata',
        'error_code',
        'error_message',
    ];

    protected $casts = [
        'session_metadata' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
