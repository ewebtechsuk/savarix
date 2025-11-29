<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    public function handle($request, Closure $next)
    {
        if (app()->runningUnitTests() || app()->environment('testing')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'webhooks/onfido',
    ];
}
