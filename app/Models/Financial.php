<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Financial extends Model
{
    protected $fillable = [
        'type', 'amount', 'date', 'description', 'property_id', 'tenancy_id', 'contact_id'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    public function tenancy()
    {
        return $this->belongsTo(SavarixTenancy::class, 'tenancy_id');
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
