<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Landlord extends Model
{
    protected $table = 'landlords';

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
    ];
}
