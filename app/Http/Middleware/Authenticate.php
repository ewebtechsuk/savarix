<?php

namespace App\Http\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;

class Authenticate
{
    public function __invoke(Request $request, callable $next, array $context): Response
    {
        $app = $context['app'];
        $auth = $app->auth();

        if (!$auth->check()) {
            return Response::redirect('/login');
        }

        return $next($request);
    }
}
