<?php

namespace App\Tenancy;

class TenantDirectory
{
    /**
     * @var array<int, array{slug: string, name: string, domains: string[]}>
     */
    private array $tenants;

    public function __construct(?array $tenants = null)
    {
        $this->tenants = $tenants ?? self::defaultTenants();
    }

    /**
     * @return array<int, array{slug: string, name: string, domains: string[]}>
     */
    public function all(): array
    {
        return $this->tenants;
    }

    private static function defaultTenants(): array
    {
        return [
            [
                'slug' => 'aktonz',
                'name' => 'Aktonz',
                'domains' => [
                    'aktonz.ressapp.localhost:8888',
                    'aktonz.darkorange-chinchilla-918430.hostingersite.com',
                ],
            ],
            [
                'slug' => 'haringeyestates',
                'name' => 'Haringey Estates',
                'domains' => [
                    'haringey.ressapp.localhost:8888',
                ],
            ],
        ];
    }
}
