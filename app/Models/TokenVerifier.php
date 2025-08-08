<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenVerifier extends Model
{
    protected $table = 'tokens';

    public $timestamps = false;

    protected $guarded = ['id'];
}
