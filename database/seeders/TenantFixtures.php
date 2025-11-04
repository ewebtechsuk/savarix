<?php

namespace Database\Seeders;

use App\Models\Tenant;

class TenantFixtures
{
    public static function seed(): void
    {
        Tenant::query()->each(static function (Tenant $tenant): void {
            $tenant->delete();
        });

        $tenants = [
            [
                'slug' => 'aktonz',
                'name' => 'Aktonz',
                'domains' => [
                    'aktonz.savirix.localhost:8888',
                    'aktonz.darkorange-chinchilla-918430.hostingersite.com',
                ],
            ],
            [
                'slug' => 'haringeyestates',
                'name' => 'Haringey Estates',
                'domains' => [
                    'haringey.savirix.localhost:8888',
                ],
            ],
            [
                'slug' => 'oakwoodhomes',
                'name' => 'Oakwood Homes',
                'domains' => [
                    'oakwood.savirix.localhost:8888',
                    'oakwoodhomes.example.com',
                ],
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::factory()->create([
                'id' => $tenantData['slug'],
            ]);

            $tenant->forceFill([
                'slug' => $tenantData['slug'],
                'name' => $tenantData['name'],
                'domains' => $tenantData['domains'],
            ])->save();

            foreach ($tenantData['domains'] as $domain) {
                $tenant->domains()->updateOrCreate(['domain' => $domain]);
            }
        }
    }
}
