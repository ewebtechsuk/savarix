<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties for the agent app.
     */
    public function index()
    {
        return view('agent.properties.index');
    }
}
