<?php

namespace App\Http\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;

class TenantAuthenticate
{
    public function __invoke(Request $request, callable $next, array $context): Response
    {
        $app = $context['app'];

        if (!$app->auth()->check()) {
            return Response::redirect('/tenant/login');
        }

        return $next($request);
    }
}
