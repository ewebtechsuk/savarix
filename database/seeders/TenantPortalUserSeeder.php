<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantPortalUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureAdminUser();
        $this->ensureTenantUser();
    }

    private function ensureAdminUser(): void
    {
        if (User::where('email', 'admin@savirix.com')->exists()) {
            return;
        }

        User::factory()->create([
            'name' => 'Portal Admin',
            'email' => 'admin@savirix.com',
            'password' => Hash::make(env('PORTAL_ADMIN_PASSWORD', 'changeme')),
            'is_admin' => true,
        ]);
    }

    private function ensureTenantUser(): void
    {
        if (User::where('email', 'tenant@aktonz.com')->exists()) {
            return;
        }

        User::factory()->create([
            'name' => 'Aktonz Tenant',
            'email' => 'tenant@aktonz.com',
            'password' => Hash::make(env('PORTAL_TENANT_PASSWORD', 'secret')),
            'is_admin' => false,
        ]);
    }
}
