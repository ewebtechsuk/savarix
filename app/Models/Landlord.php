<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Landlord extends Model
{
    protected $table = 'landlords';

    /**
     * Keep timestamps disabled so model persistence works without Carbon.
     */
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
    ];
}
