<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordAttachments extends Model
{
    protected $table = 'landlords_attachments';

    protected $guarded = ['id'];

    public $incrementing = false;

    protected $primaryKey = null;
}
