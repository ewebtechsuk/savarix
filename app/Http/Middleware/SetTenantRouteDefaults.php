<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class SetTenantRouteDefaults
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $centralDomains = $this->centralDomains();
        $tenant = null;

        foreach ($centralDomains as $centralDomain) {
            $tenant = $this->extractTenantFromHost($host, $centralDomain);

            if ($tenant !== null) {
                break;
            }
        }

        if ($tenant === null && function_exists('tenancy') && tenancy()->initialized) {
            $tenantDomain = optional(tenancy()->tenant?->domains()->first())->domain;

            if (is_string($tenantDomain)) {
                foreach ($centralDomains as $centralDomain) {
                    $tenant = $this->extractTenantFromHost($tenantDomain, $centralDomain);

                    if ($tenant !== null) {
                        break;
                    }
                }
            }
        }

        if ($tenant !== null) {
            URL::defaults(['tenant' => $tenant]);
        }

        return $next($request);
    }

    /**
     * @return array<int, string>
     */
    private function centralDomains(): array
    {
        $domains = [];
        $appUrlHost = parse_url(config('app.url'), PHP_URL_HOST);

        if (is_string($appUrlHost) && $appUrlHost !== '') {
            $domains[] = $appUrlHost;
        }

        $tenancyDomains = config('tenancy.central_domains', []);

        if (is_string($tenancyDomains)) {
            $tenancyDomains = [$tenancyDomains];
        }

        if (is_array($tenancyDomains)) {
            $domains = array_merge($domains, array_filter($tenancyDomains, fn ($value) => is_string($value) && $value !== ''));
        }

        return array_values(array_unique($domains));
    }

    private function extractTenantFromHost(string $host, string $centralDomain): ?string
    {
        if ($host === $centralDomain) {
            return null;
        }

        $suffix = '.' . ltrim($centralDomain, '.');

        if (! Str::endsWith($host, $suffix)) {
            return null;
        }

        $subdomainPart = Str::beforeLast($host, $suffix);
        $labels = array_filter(explode('.', $subdomainPart));

        return $labels[0] ?? null;
    }
}
