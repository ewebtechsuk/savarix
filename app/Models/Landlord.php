<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Landlord extends Authenticatable
{
    use HasFactory;

        protected $table = 'landlords';

        protected $guarded = array('id');

    public function contact(): HasOne
    {
        return $this->hasOne(Contact::class, 'email', 'contact_email');
    }
}