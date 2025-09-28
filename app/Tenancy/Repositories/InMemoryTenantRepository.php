<?php

namespace App\Tenancy\Repositories;

use App\Tenancy\TenantRepository;

class InMemoryTenantRepository implements TenantRepository
{
    /**
     * @param array<int, array{slug: string, name: string, domains: string[]}> $tenants
     */
    public function __construct(private array $tenants = [])
    {
    }

    /**
     * @param array<int, array{slug: string, name: string, domains: string[]}> $tenants
     */
    public function seed(array $tenants): void
    {
        $this->tenants = array_values($tenants);
    }

    /**
     * @return array<int, array{slug: string, name: string, domains: string[]}>
     */
    public function allTenants(): array
    {
        return array_values($this->tenants);
    }
}
