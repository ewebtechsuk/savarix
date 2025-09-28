<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stancl\Tenancy\Facades\Tenancy;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Only allow login from a tenant subdomain
        if (!tenant()) {
            abort(404, 'Company not found.');
        }
        return view('tenant.login');
    }

    public function login(Request $request)
    {
        if (!tenant()) {
            abort(404, 'Company not found.');
        }
        $credentials = $request->only('email', 'password');

        if (Auth::guard('tenant')->attempt($credentials)) {
            return redirect()->intended('/dashboard');
        }
        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout()
    {
        Auth::guard('tenant')->logout();

        return redirect('/tenant/login');
    }
}
