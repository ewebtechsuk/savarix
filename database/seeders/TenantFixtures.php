<?php

namespace Database\Seeders;

use App\Tenancy\Repositories\InMemoryTenantRepository;
use App\Tenancy\TenantRepositoryManager;

class TenantFixtures
{
    public static function seed(): void
    {
        $repository = new InMemoryTenantRepository([
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
            [
                'slug' => 'oakwoodhomes',
                'name' => 'Oakwood Homes',
                'domains' => [
                    'oakwood.ressapp.localhost:8888',
                    'oakwoodhomes.example.com',
                ],
            ],
        ]);

        TenantRepositoryManager::setRepository($repository);
    }
}
