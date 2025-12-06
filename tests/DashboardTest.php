<?php

namespace Tests;

use App\Services\TenantProvisioner;

class DashboardTest extends TestCase
{
    public function test_marketing_domain_does_not_expose_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertNotFound();
    }

    public function testDashboardRequiresAuthentication(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('admin.login'));

    }

    public function testAuthenticatedUserCanSeeDashboard(): void
    {
        $tenantProvisioner = app(TenantProvisioner::class);

        $email = 'test-user@example.com';

        $tenant = $tenantProvisioner->provision([
            'subdomain' => 'aktonz',
            'name' => 'Aktonz',
            'user' => [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'password',
            ],
        ])->tenant();

        $domain = $tenant->domains()->first()->domain;

        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $email)->firstOrFail();
        tenancy()->end();

        $this->useTenantDomain($domain);

        $response = $this->actingAs($user)->get('https://' . $domain . '/dashboard');
        $response->assertStatus(200)
            ->assertSee('Dashboard');

    }
}
