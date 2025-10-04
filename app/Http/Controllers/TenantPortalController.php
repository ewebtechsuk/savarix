<?php

namespace App\Http\Controllers;

use App\Tenancy\TenantDirectory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\ViewModels\TenantDashboardViewModel;

class TenantPortalController extends Controller
{
    public function __construct(private ?TenantDirectory $directory = null)
    {
        $this->directory ??= new TenantDirectory();
    }

    public function login(): View
    {
        return view('tenant.login');
    }

    public function dashboard(Request $request): View
    {
        $tenantGuard = Auth::guard('tenant');

        abort_unless($tenantGuard->check(), 403);

        $tenant = $tenantGuard->user();

        return view('tenant.dashboard', [
            'dashboard' => TenantDashboardViewModel::fromTenant($tenant),
        ]);
    }

    public function list(): View
    {
        $tenants = $this->directory?->all() ?? [];

        return view('tenant.list', [
            'tenants' => $tenants,
        ]);
    }
}
