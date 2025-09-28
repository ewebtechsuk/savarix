<?php

namespace App\Tenancy\Repositories;

use App\Models\Tenant;
use App\Tenancy\TenantRepository;

class DatabaseTenantRepository implements TenantRepository
{
    /**
     * @return array<int, array{slug: string, name: string, domains: string[]}>
     */
    public function allTenants(): array
    {
        if (!class_exists(Tenant::class)) {
            return [];
        }

        $tenants = [];

        foreach (Tenant::all() as $tenant) {
            $tenants[] = [
                'slug' => $this->resolveSlug($tenant),
                'name' => $this->resolveName($tenant),
                'domains' => $this->resolveDomains($tenant),
            ];
        }

        return $tenants;
    }

    private function resolveSlug($tenant): string
    {
        $data = $tenant->data ?? [];

        if (is_array($data) && isset($data['slug']) && is_string($data['slug'])) {
            return $data['slug'];
        }

        if (isset($tenant->id) && is_string($tenant->id)) {
            return $tenant->id;
        }

        return (string) ($tenant->slug ?? '');
    }

    private function resolveName($tenant): string
    {
        $data = $tenant->data ?? [];

        if (is_array($data)) {
            foreach (['name', 'company_name', 'label'] as $key) {
                if (isset($data[$key]) && is_string($data[$key]) && $data[$key] !== '') {
                    return $data[$key];
                }
            }
        }

        if (isset($tenant->name) && is_string($tenant->name)) {
            return $tenant->name;
        }

        return $this->resolveSlug($tenant);
    }

    /**
     * @return string[]
     */
    private function resolveDomains($tenant): array
    {
        $data = $tenant->data ?? [];
        $domains = [];

        if (is_array($data) && isset($data['domains']) && is_array($data['domains'])) {
            $domains = array_values(array_filter($data['domains'], static fn ($domain) => is_string($domain) && $domain !== ''));
        }

        if ($domains !== []) {
            return $domains;
        }

        if (method_exists($tenant, 'domains')) {
            $relation = $tenant->domains();

            if (is_iterable($relation)) {
                foreach ($relation as $domain) {
                    if (is_object($domain) && isset($domain->domain) && is_string($domain->domain)) {
                        $domains[] = $domain->domain;
                    }
                }
            } elseif (method_exists($relation, 'get')) {
                foreach ($relation->get() as $domain) {
                    if (isset($domain->domain) && is_string($domain->domain)) {
                        $domains[] = $domain->domain;
                    }
                }
            }
        }

        return $domains;
    }
}
