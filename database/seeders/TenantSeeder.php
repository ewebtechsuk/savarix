<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = [
            [
                'id' => 'aktonz',
                'name' => 'Aktonz',
                'domains' => [
                    'aktonz.ressapp.localhost:8888',
                    'aktonz.darkorange-chinchilla-918430.hostingersite.com',
                ],
            ],
            [
                'id' => 'haringeyestates',
                'name' => 'Haringey Estates',
                'domains' => [
                    'haringey.ressapp.localhost:8888',
                    'haringey.example.com',
                ],
            ],
            [
                'id' => 'demoestate',
                'name' => 'Demo Estate',
                'domains' => [
                    'demo.ressapp.localhost:8888',
                ],
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::query()->updateOrCreate(
                ['id' => $tenantData['id']],
                [
                    'data' => [
                        'slug' => $tenantData['id'],
                        'name' => $tenantData['name'],
                    ],
                ],
            );

            $tenant->domains()->delete();

            foreach ($tenantData['domains'] as $domain) {
                $tenant->domains()->create(['domain' => $domain]);
            }
        }
    }
}
