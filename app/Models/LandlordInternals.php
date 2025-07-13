<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordInternals extends Model
{
    protected $table = 'landlords_internals';

    protected $guarded = ['id'];

    public $incrementing = false;

    protected $primaryKey = null;
}
