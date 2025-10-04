<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Models\Tenant as StanclTenant;

class Tenant extends StanclTenant implements TenantWithDatabase, Authenticatable
{
    use HasDatabase;
    use HasFactory;
    use AuthenticatableTrait;

    protected $casts = [
        'data' => 'array',
    ];

    public function domains()
    {
        return $this->hasMany(\Stancl\Tenancy\Database\Models\Domain::class);
    }
}
