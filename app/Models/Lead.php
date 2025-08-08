<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'type', 'status', 'contact_id', 'property_id', 'notes', 'score'
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
