<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory;

    protected $table = 'properties';

    protected $guarded = ['id'];

    protected $fillable = [
        'type', 'status', 'owner_id', 'price', 'address', 'title', 'landlord_id', 'vendor_id', 'applicant_id',
        'latitude', 'longitude'
    ];

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
    public function landlord()
    {
        return $this->belongsTo(Contact::class, 'landlord_id');
    }
    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }
    public function media()
    {
        return $this->hasMany(PropertyMedia::class)->orderBy('order');
    }
    public function features()
    {
        return $this->hasMany(PropertyFeature::class);
    }
}

