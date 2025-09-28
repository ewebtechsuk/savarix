<?php

namespace App\Http\Controllers;

use App\Tenancy\TenantDirectory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantPortalController extends Controller
{
    public function login(): View
    {
        return view('tenant.login');
    }

    public function dashboard(Request $request): View
    {
        return view('tenant.dashboard', [
            'user' => $request->user(),
        ]);
    }

    public function list(): View
    {
        $directory = new TenantDirectory();

        return view('tenant.list', [
            'tenants' => $directory->all(),
        ]);
    }
}
