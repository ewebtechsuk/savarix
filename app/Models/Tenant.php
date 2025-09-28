<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

if (class_exists(\Stancl\Tenancy\Database\Models\Tenant::class)) {
    abstract class BaseTenant extends \Stancl\Tenancy\Database\Models\Tenant
    {
    }
} else {
    abstract class BaseTenant extends Model
    {
    }
}

class Tenant extends BaseTenant
{
    protected $table = 'tenants';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class, 'tenant_id');
    }
}
