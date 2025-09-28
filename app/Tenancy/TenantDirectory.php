<?php

namespace App\Tenancy;

class TenantDirectory
{
    private TenantRepository $repository;

    public function __construct(?TenantRepository $repository = null)
    {
        $this->repository = $repository ?? TenantRepositoryManager::getRepository();
    }

    /**
     * @return array<int, array{slug: string, name: string, domains: string[]}>
     */
    public function all(): array
    {
        return $this->repository->allTenants();
    }
}
