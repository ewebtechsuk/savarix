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
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        $guard = Auth::guard('web');

        Log::info('Admin login form rendered', [
            'guard' => 'web',
            'authenticated' => $guard->check(),
            'user_id' => $guard->user()?->id,
            'user_role' => $guard->user()?->role,
        ]);

        if ($guard->check()) {
            if ($guard->user()?->isOwner()) {
                return redirect()->route('admin.dashboard');
            }

            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

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
                $request->session()->regenerate();

                $user = $adminGuard->user();

                Log::info('Admin login successful', [
                    'user_id' => $user?->id,
                    'email' => $user?->email,
                    'role' => $user?->role,
                    'connection' => config('database.default'),
                ]);

                if ($user && $user->isOwner()) {
                    return redirect()->route('admin.dashboard');
                }

                $adminGuard->logout();
                Log::warning('Admin login rejected: user is not owner', [
                    'user_id' => $user?->id,
                    'email' => $user?->email,
                    'role' => $user?->role,
                ]);
            }
        } catch (Throwable $exception) {
            Log::error('Admin login failed', [
                'message' => $exception->getMessage(),
                'connection' => config('database.default'),
                'central_connection' => config('tenancy.database.central_connection'),
            ]);

            return back()->withErrors(['email' => 'Invalid credentials']);
        }

        Log::warning('Admin login attempt unsuccessful', [
            'email' => $credentials['email'] ?? null,
            'connection' => config('database.default'),
        ]);

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
