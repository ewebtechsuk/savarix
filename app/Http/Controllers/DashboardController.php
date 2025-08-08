<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Property;
use App\Models\Tenancy as TenancyModel;
use App\Models\Payment;
use App\Models\Lead;
use App\Models\Tenant;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $stats = Cache::remember('dashboard.stats', 60, function () {
            return [
                'property_count' => Property::count(),
                'tenancy_count' => TenancyModel::count(),
                'payment_count' => Payment::count(),
                'lead_count' => Lead::count(),
                'monthly_payments' => Payment::selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(amount) as total')
                    ->where('date', '>=', now()->subMonths(5)->startOfMonth())
                    ->groupBy('month')
                    ->orderBy('month')
                    ->pluck('total', 'month')
                    ->toArray(),
            ];
        });

        return view('dashboard.index', ['stats' => $stats]);
    }

    public function store(Request $request)
    {
        // TODO: implement dashboard storage logic
    }

    public function destroy($id)
    {
        // TODO: implement dashboard deletion logic
    }

    public function impersonate($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        // Find the admin user for this tenant in the central users table
        $admin = \App\Models\User::where('tenant_id', $tenant->id)->where('is_admin', true)->first();
        if (!$admin) {
            return redirect()->route('dashboard.index')->with('error', 'No admin user found for this tenant.');
        }
        auth()->login($admin);
        tenancy()->initialize($tenant);
        return redirect()->to('/dashboard'); // Redirect to tenant dashboard
    }
}
