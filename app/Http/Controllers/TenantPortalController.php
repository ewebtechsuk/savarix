<?php

namespace App\Http\Controllers;

use App\Tenancy\TenantDirectory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('tenant.dashboard', [
            'user' => $tenantGuard->check() ? $tenantGuard->user() : null,
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
