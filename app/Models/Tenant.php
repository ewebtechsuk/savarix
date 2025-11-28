<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Models\Tenant as StanclTenant;
use Stancl\Tenancy\Database\Models\Domain;

class Tenant extends StanclTenant implements TenantWithDatabase, Authenticatable
{
    use HasDatabase;
    use HasFactory;
    use AuthenticatableTrait;

    protected $casts = [
        'data' => 'array',
    ];

    public function getDomainsAttribute($value)
    {
        if ($this->relationLoaded('domains')) {
            return $this->getRelationValue('domains');
        }

        if (is_array($value)) {
            return collect($value)->map(function (string $domain): Domain {
                return new Domain([
                    'domain' => $domain,
                    'tenant_id' => $this->getKey(),
                ]);
            });
        }

        return $this->domains()->get();
    }

    public function domains()
    {
        return $this->hasMany(\Stancl\Tenancy\Database\Models\Domain::class);
    }
}
