<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogTenantRequest
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('production')) {
            $tenant = tenancy()->tenant;

            Log::info('Tenant HTTP initialized', [
                'host' => $request->getHost(),
                'tenant_initialized' => tenancy()->initialized,
                'tenant_id' => $tenant?->getTenantKey(),
                'tenant_domain' => $tenant?->domains()->first()?->domain,
                'tenant_data_agency_id' => $tenant?->getAttribute('agency_id') ?? $tenant?->data['agency_id'] ?? null,
            ]);
        }

        return $next($request);
    }
}
