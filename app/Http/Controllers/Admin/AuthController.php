<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        $adminGuard = Auth::guard('web');

        if ($adminGuard->attempt($credentials)) {
            if ($adminGuard->user()->isOwner()) {
                return redirect()->route('admin.dashboard');
            }

            $adminGuard->logout();
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        return redirect()->route('admin.login');
    }
}
