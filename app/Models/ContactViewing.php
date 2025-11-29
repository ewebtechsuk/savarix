<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactViewing extends Model
{
    protected $fillable = ['contact_id', 'property_id', 'user_id', 'date'];

    protected $casts = [
        'date' => 'datetime',
    ];
    public function contact() {
        return $this->belongsTo(Contact::class);
    }
    public function property() {
        return $this->belongsTo(Property::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
