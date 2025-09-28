<?php

namespace Tests;

use App\Models\User;

class TenantPortalTest extends TestCase
{
    public function testTenantDashboardRequiresAuthentication(): void
    {
        $this->get('/tenant/dashboard')
            ->assertRedirect('/tenant/login');
    }

    public function testTenantDashboardWelcomesAuthenticatedTenant(): void
    {
        $tenant = User::create([
            'id' => 1,
            'name' => 'Aktonz Tenant',
            'email' => 'tenant@aktonz.com',
            'password' => 'secret',
        ]);

        $this->actingAs($tenant, 'tenant');

        $this->get('/tenant/dashboard')
            ->assertOk()
            ->assertSee('Tenant Dashboard')
            ->assertSee('Welcome to your tenant portal!');
    }

    public function testWebAuthenticatedUsersCannotAccessTenantDashboard(): void
    {
        $user = User::create([
            'name' => 'Central User',
            'email' => 'central@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'web');

        $this->get('/tenant/dashboard')
            ->assertRedirect('/tenant/login');
    }
}
