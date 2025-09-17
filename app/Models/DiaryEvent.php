<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaryEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'date', 'start', 'end', 'type', 'user_id', 'property_id', 'contact_id', 'color'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
