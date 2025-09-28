<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.index', [
            'user' => $request->user(),
            'stats' => [
                'property_count' => 0,
                'tenancy_count' => 0,
                'lead_count' => 0,
                'payment_count' => 0,
            ],
        ]);
    }
}
