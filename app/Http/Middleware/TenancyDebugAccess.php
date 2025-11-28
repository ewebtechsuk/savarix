<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Middleware\RoleMiddleware;
use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class TenancyDebugAccess
{
    public function handle(Request $request, Closure $next)
    {
        $centralDomains = $this->centralDomains();
        $isCentral = in_array($request->getHost(), $centralDomains, true);

        if ($isCentral) {
            app(RestrictToCentralDomains::class)->handle($request, function () {
                return null;
            });

            $roleMiddleware = app(RoleMiddleware::class);

            try {
                $roleMiddleware->handle($request, function () {
                    return null;
                }, 'Admin|Landlord');
            } catch (\Throwable $exception) {
                $userRole = $request->user()?->role ?? null;
                $allowedRoles = ['Admin', 'Landlord', 'admin', 'landlord'];

                if (! in_array($userRole, $allowedRoles, true)) {
                    throw $exception;
                }

                Log::warning('Tenancy debug route allowed via fallback role check', [
                    'user_id' => $request->user()?->getAuthIdentifier(),
                    'user_role' => $userRole,
                    'exception' => get_class($exception),
                ]);
            }
        } else {
            app(PreventAccessFromCentralDomains::class)->handle($request, function () {
                return null;
            });

            try {
                app(InitializeTenancyByDomain::class)->handle($request, function () {
                    return null;
                });
            } catch (TenantCouldNotBeIdentifiedException $exception) {
                Log::warning('Unable to initialize tenancy for debug route', [
                    'host' => $request->getHost(),
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $next($request);
    }

    private function centralDomains(): array
    {
        $centralDomains = config('tenancy.central_domains', []);

        if (is_string($centralDomains)) {
            return array_filter(array_map('trim', explode(',', $centralDomains)));
        }

        if (! is_array($centralDomains)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $centralDomains)));
    }
}
