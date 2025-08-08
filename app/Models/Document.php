<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'signature_request_id',
        'signed_at',
    ];

    protected $dates = [
        'signed_at',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
