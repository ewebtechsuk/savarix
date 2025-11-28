<?php

namespace App\Services;

use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class TenancyHealthReporter
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'app_url' => config('app.url'),
            'central_domains' => $this->centralDomains(),
            'tenants_count' => Tenant::query()->count(),
            'domains_count' => Domain::query()->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inspectHost(string $host): array
    {
        $normalizedHost = $this->normalizeHost($host);
        $domain = Domain::query()->where('domain', $normalizedHost)->first();

        if (! $domain) {
            return [
                'host' => $normalizedHost,
                'found' => false,
                'tenant_id' => null,
                'status' => 'Domain not found',
            ];
        }

        $tenantId = $domain->tenant_id;
        $status = $tenantId ? 'Domain found' : 'Domain missing tenant_id';

        if ($tenantId && function_exists('tenancy')) {
            $tenancy = tenancy();
            $previousTenant = $tenancy->tenant;
            $wasInitialized = $tenancy->initialized;

            try {
                $tenancy->initialize($domain->tenant ?? $tenantId);
                $status = $tenancy->initialized ? 'Tenancy initialized' : 'Tenancy not initialized';
                $tenantId = optional($tenancy->tenant)->getTenantKey();
            } catch (\Throwable $exception) {
                $status = 'Initialization error: ' . $exception->getMessage();
            } finally {
                if ($wasInitialized && $previousTenant) {
                    $tenancy->initialize($previousTenant);
                } else {
                    $tenancy->end();
                }
            }
        }

        return [
            'host' => $normalizedHost,
            'found' => true,
            'tenant_id' => $tenantId,
            'status' => $status,
        ];
    }

    /**
     * @return array<int, string>
     */
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

    private function normalizeHost(string $host): string
    {
        $host = trim($host);

        if ($host === '') {
            return $host;
        }

        $parsed = parse_url($host);

        if ($parsed !== false && isset($parsed['host'])) {
            $host = $parsed['host'];
        }

        return rtrim($host, '/');
    }
}
