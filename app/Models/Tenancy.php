<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenancy extends Model
{
    protected $fillable = [
        'property_id', 'contact_id', 'start_date', 'end_date', 'rent', 'status', 'notes'
    ];

    protected $appends = ['tenant_contact_details', 'rent_amount'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function getTenantContactDetailsAttribute(): array
    {
        if (! $this->relationLoaded('contact')) {
            $this->load('contact');
        }

        return [
            'name' => $this->contact?->name,
            'email' => $this->contact?->email,
            'phone' => $this->contact?->phone,
        ];
    }

    public function getRentAmountAttribute()
    {
        return $this->rent;
    }
}
