<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandlordDashboardController extends Controller
{
    /**
     * Display the landlord dashboard.
     */
    public function index()
    {
        return view('landlord.dashboard');
    }
}
