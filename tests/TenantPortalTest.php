<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TenantPortalTest extends TestCase
{
    public function testTenantLoginPageLoadsSuccessfully(): void
    {
        $response = $this->get('/tenant/login');

        $response->assertStatus(200)
            ->assertSee('Tenant Login');

    }

    public function testTenantDashboardRequiresAuthentication(): void
    {
        $response = $this->get('/tenant/dashboard');

        $response->assertRedirect('/tenant/login');

    }

    public function testTenantDashboardWelcomesAuthenticatedUser(): void
    {
        $user = User::create([
            'name' => 'Aktonz Tenant',
            'email' => 'tenant@aktonz.com',
            'password' => Hash::make('secret'),
        ]);

        $response = $this->actingAs($user, 'tenant')->get('/tenant/dashboard');

        $response->assertStatus(200)
            ->assertSee('Tenant Dashboard')
            ->assertSee('Aktonz Tenant');

    }

    public function testTenantDirectoryListsKnownTenants(): void
    {
        $response = $this->get('/tenant/list');

        $response->assertStatus(200)
            ->assertSee('Tenant Directory');


        foreach (['Aktonz', 'Haringey Estates', 'Oakwood Homes'] as $tenantName) {
            $response->assertSee($tenantName);
        }

        foreach ([
            'aktonz.darkorange-chinchilla-918430.hostingersite.com',
            'haringey.ressapp.localhost:8888',
            'oakwoodhomes.example.com',
        ] as $domain) {
            $response->assertSee($domain);
        }
    }
}
