<?php

namespace Database\Seeders;

use App\Models\Landlord;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthGuardAccountSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'staff@ressapp.com'],
            [
                'name' => 'Staff User',
                'password' => Hash::make(env('STAFF_USER_PASSWORD', 'password')),
                'is_admin' => true,
            ]
        );

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

        $tenant->domains()->updateOrCreate(
            ['domain' => 'aktonz.ressapp.localhost:8888'],
            []
        );

        $tenant->domains()->updateOrCreate(
            ['domain' => 'aktonz.darkorange-chinchilla-918430.hostingersite.com'],
            []
        );

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

        $haringey->domains()->updateOrCreate(
            ['domain' => 'haringey.ressapp.localhost:8888'],
            []
        );
    }
}
