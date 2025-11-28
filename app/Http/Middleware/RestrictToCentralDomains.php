<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictToCentralDomains
{
    public function handle(Request $request, Closure $next)
    {
        $centralDomains = config('tenancy.central_domains', []);

        if (is_string($centralDomains)) {
            $centralDomains = array_filter(array_map('trim', explode(',', $centralDomains)));
        }

        if (! in_array($request->getHost(), $centralDomains, true)) {
            abort(404);
        }

        return $next($request);
    }
}
