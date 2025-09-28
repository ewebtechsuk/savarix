<?php

namespace Tests;

use App\Models\User;
use Illuminate\Auth\GenericUser;

class TenantPortalTest extends TestCase
{
    public function testTenantDashboardRequiresAuthentication(): void
    {
        $this->get('/tenant/dashboard')
            ->assertRedirectedTo('login');
    }

    public function testTenantDashboardWelcomesAuthenticatedTenant(): void
    {
        $tenant = new GenericUser([
            'id' => 1,
            'name' => 'Aktonz Tenant',
            'email' => 'tenant@aktonz.com',
        ]);

        $this->actingAs($tenant, 'tenant');

        $this->get('/tenant/dashboard')
            ->assertResponseOk()
            ->see('Tenant Dashboard')
            ->see('Welcome to your tenant portal!');
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
            ->assertRedirectedTo('login');

    }
}
