<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordBanks extends Model
{
    protected $table = 'landlords_banks';

    protected $guarded = ['id'];

    public $incrementing = false;

    protected $primaryKey = null;
}
