<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyMedia extends Model
{
    protected $fillable = ['property_id', 'file_path', 'type', 'media_type', 'order', 'is_featured'];

    protected $attributes = [
        'media_type' => 'photo',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
