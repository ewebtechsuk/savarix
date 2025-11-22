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
        if (Auth::guard($guard)->check()) {
            $adminPath = trim(env('SAVARIX_ADMIN_PATH', 'savarix-admin'), '/');

            if ($request->is($adminPath) || $request->is($adminPath.'/*')) {
                return redirect()->route('admin.dashboard');
            }

            return redirect('/dashboard');
        }

        return $next($request);
    }
}
