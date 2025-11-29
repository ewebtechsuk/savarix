<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Applicant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'min_budget',
        'max_budget',
        'preferred_bedrooms',
        'preferred_city',
        'marketing_opt_in',
    ];

    protected $casts = [
        'min_budget' => 'decimal:2',
        'max_budget' => 'decimal:2',
        'preferred_bedrooms' => 'integer',
        'marketing_opt_in' => 'boolean',
    ];
}
