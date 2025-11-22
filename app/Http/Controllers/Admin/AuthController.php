<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        $centralConnection = config('tenancy.database.central_connection', config('database.default'));
        config(['database.default' => $centralConnection]);
        DB::setDefaultConnection($centralConnection);

        Auth::shouldUse('web');
        $adminGuard = Auth::guard('web');

        try {
            if ($adminGuard->attempt($credentials)) {
                if ($adminGuard->user()->isOwner()) {
                    return redirect()->route('admin.dashboard');
                }

                $adminGuard->logout();
            }
        } catch (Throwable $exception) {
            Log::error('Admin login failed', [
                'message' => $exception->getMessage(),
                'connection' => config('database.default'),
                'central_connection' => config('tenancy.database.central_connection'),
            ]);

            return back()->withErrors(['email' => 'Invalid credentials']);
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
