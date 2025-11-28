<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

class TenantDomainSynchronizer
{
    public function syncForAgency(Agency $agency): ?Tenant
    {
        $domainHost = Agency::normalizeDomain($agency->domain);

        if (! $domainHost) {
            return null;
        }

        $tenantId = $this->deriveTenantId($agency, $domainHost);

        /** @var Tenant $tenant */
        $tenant = Tenant::query()->firstOrCreate(['id' => $tenantId]);

        $domain = Domain::query()->updateOrCreate(
            ['domain' => $domainHost],
            ['tenant_id' => $tenant->getKey()]
        );

        Log::info('Tenant domain synchronised for agency', [
            'agency_id' => $agency->id,
            'tenant_id' => $tenant->getKey(),
            'domain_id' => $domain->id,
            'domain' => $domainHost,
        ]);

        return $tenant;
    }

    public function findTenantIdForAgency(Agency $agency): ?string
    {
        $domainHost = Agency::normalizeDomain($agency->domain);

        if (! $domainHost) {
            return null;
        }

        return $this->deriveTenantId($agency, $domainHost);
    }

    protected function deriveTenantId(Agency $agency, string $domainHost): string
    {
        $centralDomains = array_filter(
            config('tenancy.central_domains', []),
            static fn ($value) => is_string($value) && $value !== ''
        );

        foreach ($centralDomains as $centralDomain) {
            $suffix = '.' . ltrim($centralDomain, '.');

            if (Str::endsWith($domainHost, $suffix)) {
                $candidate = Str::beforeLast($domainHost, $suffix);

                if ($candidate !== '') {
                    return $candidate;
                }
            }
        }

        $firstLabel = explode('.', $domainHost)[0] ?? null;

        if (is_string($firstLabel) && $firstLabel !== '') {
            return $firstLabel;
        }

        if (is_string($agency->slug) && $agency->slug !== '') {
            return $agency->slug;
        }

        return (string) $agency->getKey();
    }
}
