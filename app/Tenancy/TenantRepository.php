<?php

namespace App\Tenancy;

/**
 * @internal Simple repository contract for fetching tenant directory data.
 */
interface TenantRepository
{
    /**
     * @return array<int, array{slug: string, name: string, domains: string[]}>
     */
    public function allTenants(): array;
}
