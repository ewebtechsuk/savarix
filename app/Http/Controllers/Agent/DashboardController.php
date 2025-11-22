<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display the agent dashboard.
     */
    public function index()
    {
        return view('agent.dashboard.index', [
            'openPropertiesCount' => 12,
            'todayViewingsCount' => 5,
            'openTasksCount' => 18,
        ]);
    }
}
