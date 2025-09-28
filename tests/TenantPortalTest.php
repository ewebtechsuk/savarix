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

        $response = $this->actingAs($user)->get('/tenant/dashboard');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Tenant Dashboard');
        $this->assertSee($response, 'Aktonz Tenant');
    }

    public function testTenantDirectoryListsKnownTenants(): void
    {
        $response = $this->get('/tenant/list');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Tenant Directory');
        $this->assertSee($response, 'Aktonz');
        $this->assertSee($response, 'aktonz.darkorange-chinchilla-918430.hostingersite.com');
        $this->assertSee($response, 'Haringey Estates');
        $this->assertSee($response, 'haringey.example.com');
        $this->assertSee($response, 'Demo Estate');
        $this->assertSee($response, 'demo.ressapp.localhost:8888');
    }
}
