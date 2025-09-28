<?php

namespace Stancl\Tenancy\Database\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $table = 'domains';

    /**
     * Disable timestamps since the minimal testing setup does not provide the
     * full Carbon integration expected by Eloquent.
     */
    public $timestamps = false;

    protected $fillable = [
        'domain',
        'tenant_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
