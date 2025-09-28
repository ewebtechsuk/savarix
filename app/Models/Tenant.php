<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as StanclTenant;

class Tenant extends StanclTenant
{
    protected $fillable = [
        'id',
        'email',
        'password',
        'data',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    // Add any future custom logic or relationships here
    public function domains()
    {
        return $this->hasMany(\Stancl\Tenancy\Database\Models\Domain::class);
    }
}
