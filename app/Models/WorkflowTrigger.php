<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkflowTrigger extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'type',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}
