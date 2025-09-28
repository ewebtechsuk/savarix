<?php

namespace App\Http\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function __invoke(Request $request, callable $next, array $context, string ...$guards): Response
    {
        $guards = $guards ?: ['web'];

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::shouldUse($guard);

                return $next($request);
            }
        }

        $guard = $guards[0];
        $loginRoute = $guard === 'tenant' ? '/tenant/login' : '/login';

        return Response::redirect($loginRoute);
    }
}
