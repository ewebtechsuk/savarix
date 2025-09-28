<?php

namespace Tests\Support\Middleware;

use Closure;
use Illuminate\Http\Request;

class BypassTenancy
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        return $next($request);
    }
}
