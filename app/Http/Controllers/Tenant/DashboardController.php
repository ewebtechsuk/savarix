<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        if (!tenant()) {
            abort(404, 'Company not found.');
        }
        if (!Auth::guard('tenant')->check()) {
            return redirect('/tenant/login');
        }
        return view('tenant.dashboard');
    }
}
