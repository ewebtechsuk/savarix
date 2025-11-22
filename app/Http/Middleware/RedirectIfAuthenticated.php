<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $guard = $guard ?? 'web';

        if (Auth::guard($guard)->check()) {
            $adminPath = trim(env('SAVARIX_ADMIN_PATH', 'savarix-admin'), '/');

            // If an authenticated owner hits an admin login route, send them to the owner dashboard
            if ($request->is($adminPath) || $request->is($adminPath.'/*') || $request->routeIs('admin.*')) {
                return redirect()->route('admin.dashboard');
            }

            // Otherwise, send authenticated web users to the central dashboard
            if ($guard === 'web') {
                return redirect('/dashboard');
            }
        }

        return $next($request);
    }
}
