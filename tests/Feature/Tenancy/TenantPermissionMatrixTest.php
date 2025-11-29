<?php

namespace Tests\Feature\Tenancy;

use App\Services\TenantProvisioner;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantPermissionMatrixTest extends TestCase
{
    public function test_owner_gets_full_permissions_and_can_access_routes(): void
    {
        $provisioner = app(TenantProvisioner::class);
        $email = 'owner+' . Str::random(6) . '@example.com';

        $result = $provisioner->provision([
            'subdomain' => 'perm-' . Str::random(6),
            'name' => 'Permission Test Agency',
            'user' => [
                'name' => 'Permission Owner',
                'email' => $email,
                'password' => 'secret1234',
            ],
        ]);

        $tenant = $result->tenant();
        $this->assertNotNull($tenant);

        tenancy()->initialize($tenant);

        try {
            $owner = app(config('auth.providers.users.model'))::where('email', $email)->first();
            $this->assertNotNull($owner);

            $this->assertTrue($owner->hasRole('Admin'));
            $this->assertTrue($owner->hasRole('Landlord'));
            $this->assertTrue($owner->hasRole('Agent'));
            $this->assertTrue($owner->hasPermissionTo('properties.create'));
            $this->assertTrue($owner->hasPermissionTo('contacts.view'));
        } finally {
            tenancy()->end();
        }

        $domain = $tenant->domains()->first()?->domain;
        $this->assertNotNull($domain);

        tenancy()->initialize($tenant);

        try {
            $server = ['HTTP_HOST' => $domain, 'SERVER_NAME' => $domain];
            $this->withServerVariables($server);

            $this->actingAs($owner)
                ->get('http://' . $domain . '/contacts')
                ->assertOk();

            $this->actingAs($owner)
                ->get('http://' . $domain . '/properties/create')
                ->assertOk();

            $this->actingAs($owner)
                ->post('http://' . $domain . '/properties', [
                    'title' => 'Matrix Property',
                ])
                ->assertRedirect();
        } finally {
            tenancy()->end();
        }
    }

    public function test_default_owner_created_when_no_user_payload(): void
    {
        $provisioner = app(TenantProvisioner::class);
        $subdomain = 'auto-' . Str::random(6);

        $result = $provisioner->provision([
            'subdomain' => $subdomain,
            'name' => 'Auto Owner Agency',
        ]);

        $tenant = $result->tenant();
        $this->assertNotNull($tenant);

        tenancy()->initialize($tenant);

        try {
            $owner = app(config('auth.providers.users.model'))::where('email', sprintf('owner@%s.local', $subdomain))->first();
            $this->assertNotNull($owner);
            $this->assertTrue($owner->hasRole('Admin'));
            $this->assertTrue($owner->hasRole('Landlord'));
            $this->assertTrue($owner->hasPermissionTo('properties.view'));
        } finally {
            tenancy()->end();
        }
    }
}
