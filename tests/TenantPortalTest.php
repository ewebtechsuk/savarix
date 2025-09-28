<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

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
        $tenant = Tenant::create([
            'id' => 'test-tenant',
            'email' => 'tenant@aktonz.com',
            'password' => Hash::make('secret'),
            'data' => [
                'name' => 'Aktonz Tenant',
            ],
        ]);

        $response = $this->actingAs($tenant, 'tenant')->get('/tenant/dashboard');

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
    }
}
