<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenancy;
use App\Models\Invoice;

class Payment extends Model
{
    protected $fillable = [
        'tenancy_id',
        'date',
        'invoice_id',
        'amount',
        'method',
        'notes',
        'status',
        'stripe_reference',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
