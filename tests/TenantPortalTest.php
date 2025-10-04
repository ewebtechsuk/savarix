<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain as TenantDomain;

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
        $tenant = Tenant::factory()->create([
            'id' => 'aktonz-' . Str::random(6),
            'name' => 'Aktonz Tenant',
        ]);

        Auth::guard('tenant')->loginUsingId($tenant->getKey());

        $response = $this->get('/tenant/dashboard');

        $response->assertStatus(200)
            ->assertSee('Welcome back, Aktonz Tenant!', false)
            ->assertSee("Here's the latest activity across your tenancy.", false);

    }

    public function testTenantDirectoryListsKnownTenants(): void
    {
        foreach ([
            ['slug' => 'aktonz', 'name' => 'Aktonz', 'domain' => 'aktonz.example.test'],
            ['slug' => 'haringey-estates', 'name' => 'Haringey Estates', 'domain' => 'haringey.example.test'],
            ['slug' => 'oakwood-homes', 'name' => 'Oakwood Homes', 'domain' => 'oakwoodhomes.example.test'],
        ] as $seed) {
            $tenant = Tenant::factory()->create([
                'id' => $seed['slug'] . '-' . Str::random(4),
                'name' => $seed['name'],
                'data' => [
                    'slug' => $seed['slug'],
                    'name' => $seed['name'],
                ],
            ]);

            TenantDomain::create([
                'domain' => $seed['domain'],
                'tenant_id' => $tenant->id,
            ]);
        }

        $response = $this->get('/tenant/list');

        $response->assertStatus(200)
            ->assertSee('Tenant Directory');

        $response->assertSee('Aktonz');
        $response->assertSee('aktonz.example.test');
        $response->assertSee('Haringey Estates');
        $response->assertSee('haringey.example.test');
        $response->assertSee('Oakwood Homes');
        $response->assertSee('oakwoodhomes.example.test');
    }
}
