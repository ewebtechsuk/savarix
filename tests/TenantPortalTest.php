<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TenantPortalTest extends TestCase
{
    use DatabaseMigrations;

    public function testTenantLoginPageLoadsSuccessfully()
    {
        $this->get('/tenant/login')
            ->assertOk()
            ->assertSee('Tenant Login');
    }

    public function testTenantDashboardRequiresAuthentication()
    {
        $this->get('/tenant/dashboard')
            ->assertRedirect('/tenant/login');
    }

    public function testTenantDashboardWelcomesAuthenticatedUser()
    {
        $user = User::factory()->create([
            'name' => 'Aktonz Tenant',
        ]);

        $this->actingAs($user)
            ->get('/tenant/dashboard')
            ->assertOk()
            ->assertSee('Tenant Dashboard')
            ->assertSee('Aktonz Tenant');
    }

    public function testTenantDirectoryListsKnownTenants()
    {
        $this->get('/tenant/list')
            ->assertOk()
            ->assertSee('Tenant Directory')
            ->assertSee('Aktonz')
            ->assertSee('aktonz.darkorange-chinchilla-918430.hostingersite.com');
    }
}
