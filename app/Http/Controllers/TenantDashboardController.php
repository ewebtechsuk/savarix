<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TenantDashboardController extends Controller
{
    /**
     * Display the tenant dashboard.
     */
    public function index()
    {
        return view('tenant.dashboard');
    }
}
