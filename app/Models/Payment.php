<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenancy;

class Payment extends Model
{
    protected $fillable = [
        'tenancy_id',
        'amount',
        'status',
        'stripe_reference',
    ];

    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }
}
