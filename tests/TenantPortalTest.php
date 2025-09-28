<?php

namespace Tests;

use App\Models\User;

class TenantPortalTest extends TestCase
{
    public function testTenantLoginPageLoadsSuccessfully(): void
    {
        $response = $this->get('/tenant/login');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Tenant Login');
    }

    public function testTenantDashboardRequiresAuthentication(): void
    {
        $response = $this->get('/tenant/dashboard');

        $this->assertRedirect($response, '/tenant/login');
    }

    public function testTenantDashboardWelcomesAuthenticatedUser(): void
    {
        $user = User::create([
            'name' => 'Aktonz Tenant',
            'email' => 'tenant@aktonz.com',
            'password' => 'secret',
        ]);

        $response = $this->actingAs($user, 'tenant')->get('/tenant/dashboard');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Tenant Dashboard');
        $this->assertSee($response, 'Aktonz Tenant');
    }

    public function testTenantDirectoryListsKnownTenants(): void
    {
        $response = $this->get('/tenant/list');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Tenant Directory');

        foreach (['Aktonz', 'Haringey Estates', 'Oakwood Homes'] as $tenantName) {
            $this->assertSee($response, $tenantName);
        }

        foreach ([
            'aktonz.darkorange-chinchilla-918430.hostingersite.com',
            'haringey.ressapp.localhost:8888',
            'oakwoodhomes.example.com',
        ] as $domain) {
            $this->assertSee($response, $domain);
        }
    }
}
