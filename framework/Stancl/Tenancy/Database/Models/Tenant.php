<?php

namespace Stancl\Tenancy\Database\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $table = 'tenants';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'email',
        'password',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function domains()
    {
        return $this->hasMany(Domain::class, 'tenant_id');
    }
}
