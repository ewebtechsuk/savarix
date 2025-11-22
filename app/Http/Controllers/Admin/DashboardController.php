<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $agencyCount = Agency::count();
        $activeAgencies = Agency::where('status', 'active')->count();
        $totalAgencyUsers = User::whereNotNull('agency_id')->count();

        $recentEvents = collect();

        Agency::orderByDesc('created_at')->take(5)->each(function (Agency $agency) use ($recentEvents): void {
            $recentEvents->push([
                'agency' => $agency->name,
                'type' => 'Agency created',
                'description' => $agency->email ?? 'No primary email set',
                'time' => $agency->created_at,
            ]);
        });

        User::whereNotNull('agency_id')->orderByDesc('created_at')->take(5)->each(function (User $user) use ($recentEvents): void {
            $recentEvents->push([
                'agency' => $user->agency?->name ?? 'Unknown agency',
                'type' => 'New user invited',
                'description' => $user->email,
                'time' => $user->created_at,
            ]);
        });

        $recentActivity = $recentEvents
            ->sortByDesc('time')
            ->take(5)
            ->values();

        $lastActivity = $recentActivity->first();

        return view('admin.dashboard.index', [
            'agencyCount' => $agencyCount,
            'activeAgencies' => $activeAgencies,
            'totalAgencyUsers' => $totalAgencyUsers,
            'lastActivity' => $lastActivity,
            'recentActivity' => $recentActivity,
        ]);
    }
}
