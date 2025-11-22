<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks and diary items for the agent app.
     */
    public function index()
    {
        return view('agent.tasks.index');
    }
}
