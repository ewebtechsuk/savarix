<?php

namespace Database\Seeders;

use App\Models\Landlord;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthGuardAccountSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->tableExists((new User())->getTable())) {
            User::updateOrCreate(
                ['email' => 'staff@ressapp.com'],
                [
                    'name' => 'Staff User',
                    'password' => Hash::make(env('STAFF_USER_PASSWORD', 'password')),
                    'is_admin' => true,
                ]
            );
        }

        if ($this->tableExists((new Landlord())->getTable())) {
            Landlord::updateOrCreate(
                ['contact_email' => 'landlord@ressapp.com'],
                [
                    'person_firstname' => 'Lana',
                    'person_lastname' => 'Lord',
                    'person_landlord' => 1,
                    'person_type' => 'individual',
                    'password' => Hash::make(env('LANDLORD_PASSWORD', 'password')),
                ]
            );
        }

        if (! $this->tableExists((new Tenant())->getTable())) {
            return;
        }

        $tenant = Tenant::firstOrNew(['id' => 'aktonz']);
        $tenant->email = 'tenant@aktonz.com';
        $tenant->password = Hash::make(env('TENANT_PASSWORD', 'password'));
        $tenantData = $tenant->data ?? [];
        $tenantData['name'] = 'Aktonz Tenant';
        $tenantData['domains'] = [
            'aktonz.ressapp.localhost:8888',
            'aktonz.darkorange-chinchilla-918430.hostingersite.com',
        ];
        $tenant->data = $tenantData;
        $tenant->save();

        $this->syncTenantDomains($tenant, [
            'aktonz.ressapp.localhost:8888',
            'aktonz.darkorange-chinchilla-918430.hostingersite.com',
        ]);

        $haringey = Tenant::firstOrNew(['id' => 'haringeyestates']);
        $haringey->email = 'tenant@haringeyestates.com';
        $haringey->password = Hash::make(env('HARINGEY_TENANT_PASSWORD', 'password'));
        $haringeyData = $haringey->data ?? [];
        $haringeyData['name'] = 'Haringey Estates';
        $haringeyData['domains'] = [
            'haringey.ressapp.localhost:8888',
        ];
        $haringey->data = $haringeyData;
        $haringey->save();

        $this->syncTenantDomains($haringey, [
            'haringey.ressapp.localhost:8888',
        ]);
    }

    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    /**
     * @param array<int, string> $domains
     */
    private function syncTenantDomains(Tenant $tenant, array $domains): void
    {
        if (! $this->tableExists('domains')) {
            return;
        }

        foreach ($domains as $domain) {
            $tenant->domains()->updateOrCreate(['domain' => $domain], []);
        }
    }
}
