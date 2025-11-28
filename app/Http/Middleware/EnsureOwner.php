<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOwner
{
    public function handle(Request $request, Closure $next)
    {
        Auth::shouldUse('web');

        $guard = Auth::guard('web');
        $user = $guard->user();

        if (! $user) {
            return redirect()->guest(route('admin.login'));
        }

        if (! $user->isOwner()) {
            $guard->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors(['email' => 'You are not authorised to access the owner admin area.']);
        }

        return $next($request);
    }
}
