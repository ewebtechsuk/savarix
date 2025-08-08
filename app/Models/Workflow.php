<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    public function triggers()
    {
        return $this->hasMany(WorkflowTrigger::class);
    }

    public function actions()
    {
        return $this->hasMany(WorkflowAction::class)->orderBy('order');
    }
}
