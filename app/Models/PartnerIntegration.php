<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'provider',
        'type',
        'credentials',
        'settings',
        'active',
    ];

    protected $casts = [
        'credentials' => 'array',
        'settings' => 'array',
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

