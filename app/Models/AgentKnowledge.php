<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentKnowledge extends Model
{
    use HasFactory;

    protected $table = 'agent_knowledge';

    protected $fillable = [
        'role',
        'type',
        'category',
        'trigger',
        'action',
        'description',
    ];

    public $timestamps = false;
}
