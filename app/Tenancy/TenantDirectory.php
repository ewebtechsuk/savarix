<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class TenantDirectory
{
    /**
     * @return array<int, array{slug: string, name: string, domains: string[]}>
     */
    public function all(): array
    {
        $tenants = $this->resolveTenants();

        return $tenants
            ->map(function (Tenant $tenant): array {
                $data = $this->normalizeData($tenant->data ?? null);
                $slug = $this->determineSlug($tenant, $data);
                $name = $this->determineName($tenant, $data, $slug);

                $domains = $tenant->domains
                    ->pluck('domain')
                    ->filter(static fn ($domain) => is_string($domain) && $domain !== '')
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

                return [
                    'slug' => $slug,
                    'name' => $name,
                    'domains' => $domains,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed>|string|null $data
     * @return array<string, mixed>
     */
    private function normalizeData(array|string|null $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_string($data) && $data !== '') {
            $decoded = json_decode($data, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * @return Collection<int, Tenant>
     */
    private function resolveTenants(): Collection
    {
        try {
            return Tenant::query()
                ->with('domains')
                ->orderBy('id')
                ->get();
        } catch (Throwable $exception) {
            return new Collection();
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function determineSlug(Tenant $tenant, array $data): string
    {
        $slug = $data['slug'] ?? null;

        if (is_string($slug) && $slug !== '') {
            return $slug;
        }

        $tenantKey = method_exists($tenant, 'getTenantKey')
            ? $tenant->getTenantKey()
            : $tenant->getAttribute('id');

        return is_string($tenantKey) ? $tenantKey : (string) $tenantKey;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function determineName(Tenant $tenant, array $data, string $slug): string
    {
        $name = $data['name'] ?? null;

        if (is_string($name) && $name !== '') {
            return $name;
        }

        $tenantName = method_exists($tenant, 'getAttribute') ? $tenant->getAttribute('name') : null;
        if (is_string($tenantName) && $tenantName !== '') {
            return $tenantName;
        }

        return $slug !== '' ? $slug : 'Unknown Tenant';
    }
}
