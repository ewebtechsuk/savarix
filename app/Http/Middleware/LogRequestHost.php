<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogRequestHost
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $response = $next($request);

        $route = $request->route();
        $routeName = method_exists($route, 'getName') ? $route?->getName() : null;
        $hasTenancyHelper = function_exists('tenancy');
        $tenancyInitialized = $hasTenancyHelper && tenancy()->initialized;
        $tenantId = $tenancyInitialized ? optional(tenancy()->tenant)->getTenantKey() : null;
        $dbConnection = DB::connection();

        $context = [
            'host' => $request->getHost(),
            'path' => $request->getPathInfo(),
            'full_url' => $request->fullUrl(),
            'route_name' => $routeName,
            'tenancy_helper_available' => $hasTenancyHelper,
            'tenancy_initialized' => $tenancyInitialized,
            'tenant_id' => $tenantId,
            'db_connection' => $dbConnection->getName(),
            'db_database' => $dbConnection->getDatabaseName(),
        ];

        $includeConfig = $this->isDashboardPath($request) || ! $tenancyInitialized;

        if ($includeConfig) {
            $context['app_url'] = config('app.url');
            $context['central_domains'] = config('tenancy.central_domains');
        }

        if ($this->isDashboardPath($request)) {
            $authenticatedGuard = null;
            $userId = null;
            $userRole = null;

            foreach (array_keys(config('auth.guards', [])) as $guard) {
                $guardInstance = Auth::guard($guard);

                if ($guardInstance->check()) {
                    $authenticatedGuard = $guard;
                    $user = $guardInstance->user();
                    $userId = $user?->getAuthIdentifier();
                    $userRole = $user->role ?? null;
                    break;
                }
            }

            $context['guard'] = $authenticatedGuard;
            $context['user_id'] = $userId;
            $context['user_role'] = $userRole;
        }

        Log::info('Request host debug', $context);

        return $response;
    }

    private function shouldSkip(Request $request): bool
    {
        $path = $request->getPathInfo();

        if ($path === '/favicon.ico') {
            return true;
        }

        return str_contains($path, 'marker-icon') || str_contains($path, 'marker-shadow');
    }

    private function isDashboardPath(Request $request): bool
    {
        return $request->is('dashboard') || $request->is('dashboard/*');
    }
}
