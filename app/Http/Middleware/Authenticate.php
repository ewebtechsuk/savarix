<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        $adminPath = trim(env('SAVARIX_ADMIN_PATH', 'savarix-admin'), '/');

        if ($request->is($adminPath) || $request->is($adminPath.'/*') || $request->routeIs('admin.*')) {
            return route('admin.login');
        }
        
        $host = $request->getHost();
        $marketingHost = parse_url(config('app.url'), PHP_URL_HOST);

        if ($marketingHost && $host !== $marketingHost) {
            return route('tenant.login');
        }

        return route('marketing.home');
    }

    /**
     * Specify the default guard when none is explicitly provided.
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            // Default to the central web guard for owner routes unless explicitly overridden.
            $guards = ['web'];
        }

        if (in_array('tenant', $guards, true)) {
            // Tenant routes use the tenant guard; otherwise stick with the web guard.
            Auth::shouldUse('tenant');
        } else {
            Auth::shouldUse('web');
        }

        parent::authenticate($request, $guards);
    }
}
