<?php

namespace App\Http\Controllers;

use App\ViewModels\LandlordDashboardViewModel;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class LandlordDashboardController extends Controller
{
    /**
     * Display the landlord dashboard.
     */
    public function index(): View
    {
        $landlordGuard = Auth::guard('landlord');

        abort_unless($landlordGuard->check(), 403);

        $landlord = $landlordGuard->user();

        return view('landlord.dashboard', [
            'dashboard' => LandlordDashboardViewModel::fromLandlord($landlord),
        ]);
    }
}
