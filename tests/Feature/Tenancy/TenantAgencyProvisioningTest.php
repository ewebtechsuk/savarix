<?php

namespace Tests\Feature\Tenancy;

use App\Services\TenantProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantAgencyProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_provisioning_seeds_roles_and_assigns_owner(): void
    {
        $provisioner = app(TenantProvisioner::class);
        $ownerEmail = 'owner@example.com';
        $subdomain = 'agency-' . Str::random(6);

        $result = $provisioner->provision([
            'subdomain' => $subdomain,
            'name' => 'Test Agency',
            'company_name' => 'Test Co',
            'data' => [],
            'user' => [
                'name' => 'Agency Owner',
                'email' => $ownerEmail,
                'password' => 'password',
            ],
        ]);

        $tenant = $result->tenant();

        $this->assertNotNull($tenant, 'Tenant was not created');
        $domain = $tenant?->domains()->first()?->domain;
        $this->assertNotNull($domain, 'Tenant domain was not created');

        tenancy()->initialize($tenant);

        try {
            $this->assertDatabaseHas('roles', ['name' => 'Admin']);
            $this->assertDatabaseHas('roles', ['name' => 'Tenant']);
            $this->assertDatabaseHas('permissions', ['name' => 'properties.view']);

            $owner = app(config('auth.providers.users.model'))::where('email', $ownerEmail)->first();

            $this->assertNotNull($owner, 'Owner user was not created');
            $this->assertTrue($owner->hasRole('Admin'));
            $this->assertTrue($owner->hasRole('Tenant'));
            $this->assertTrue($owner->hasRole('Landlord'));
            $this->assertTrue($owner->hasPermissionTo('properties.view'));
        } finally {
            tenancy()->end();
        }

        $this->actingAs($owner)
            ->get('http://' . $domain . route('contacts.index', [], false))
            ->assertOk();

        $this->actingAs($owner)
            ->get('http://' . $domain . route('properties.index', [], false))
            ->assertOk();
    }
}
