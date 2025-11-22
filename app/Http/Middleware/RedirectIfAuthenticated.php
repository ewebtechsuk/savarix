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
            $user = Auth::guard($guard)->user();

            if ($request->is(sprintf('%s/*', env('SAVARIX_ADMIN_PATH', 'savarix-admin'))) ||
                $request->routeIs('admin.*')) {
                return $user && $user->isOwner()
                    ? redirect()->route('admin.dashboard')
                    : redirect('/dashboard');
            }

            return redirect('/dashboard');
        }

        return $next($request);
    }
}
