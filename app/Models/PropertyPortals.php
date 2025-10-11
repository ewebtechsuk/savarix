<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyPortals extends Model
{
    protected $table = 'properties_portals';

    protected $guarded = ['id'];

    public const PORTALS = [
        'find_a_property',
        'globrix',
        'gumtree',
        'home_hunter',
        'homes24',
        'look_a_property',
        'movehut',
        'market',
        'primelocation',
        'property_finder',
        'property_index',
        'propertylive',
        'rightmove',
        'rightmove_overseas',
        'zoomf',
        'zoopla',
        'zoopla_overseas',
    ];

    protected $casts = [
        'find_a_property' => 'boolean',
        'globrix' => 'boolean',
        'gumtree' => 'boolean',
        'home_hunter' => 'boolean',
        'homes24' => 'boolean',
        'look_a_property' => 'boolean',
        'movehut' => 'boolean',
        'market' => 'boolean',
        'primelocation' => 'boolean',
        'property_finder' => 'boolean',
        'property_index' => 'boolean',
        'propertylive' => 'boolean',
        'rightmove' => 'boolean',
        'rightmove_overseas' => 'boolean',
        'zoomf' => 'boolean',
        'zoopla' => 'boolean',
        'zoopla_overseas' => 'boolean',
    ];
}

