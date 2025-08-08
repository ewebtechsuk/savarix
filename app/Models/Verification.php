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
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
