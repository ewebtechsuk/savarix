<?php

namespace Tests\Feature;

use App\Services\TenantProvisioner;
use App\Support\AgencyRoles;
use Database\Seeders\RolePermissionConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PropertyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_create_accessible_for_property_manager_roles(): void
    {
        $tenantProvisioner = app(TenantProvisioner::class);
        $email = 'manager@example.com';

        $tenant = $tenantProvisioner->provision([
            'subdomain' => 'agency-' . Str::random(6),
            'name' => 'Property Agency',
            'user' => [
                'name' => 'Property Manager',
                'email' => $email,
                'password' => 'password',
            ],
        ])->tenant();

        $this->assertNotNull($tenant, 'Tenant was not provisioned.');

        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $email)->firstOrFail();
        $user->syncRoles([AgencyRoles::tenantOwnerRole()]);
        tenancy()->end();

        $domain = $tenant->domains()->first()->domain;
        $this->useTenantDomain($domain);

        $this->actingAs($user)
            ->get('http://' . $domain . route('properties.create', [], false))
            ->assertOk();
    }

    public function test_property_create_accessible_for_agency_admin_role(): void
    {
        $tenantProvisioner = app(TenantProvisioner::class);
        $email = 'agency-admin@example.com';

        $tenant = $tenantProvisioner->provision([
            'subdomain' => 'agency-' . Str::random(6),
            'name' => 'Property Agency',
            'user' => [
                'name' => 'Agency Admin',
                'email' => $email,
                'password' => 'password',
            ],
        ])->tenant();

        $this->assertNotNull($tenant, 'Tenant was not provisioned.');

        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $email)->firstOrFail();

        $guard = RolePermissionConfig::guard();
        Role::query()->firstOrCreate(['name' => 'agency_admin', 'guard_name' => $guard]);
        $user->syncRoles(['agency_admin']);
        tenancy()->end();

        $domain = $tenant->domains()->first()->domain;
        $this->useTenantDomain($domain);

        $this->actingAs($user)
            ->get('http://' . $domain . route('properties.create', [], false))
            ->assertOk();
    }

    public function test_property_create_forbidden_for_users_without_roles(): void
    {
        $tenantProvisioner = app(TenantProvisioner::class);
        $email = 'viewer@example.com';

        $tenant = $tenantProvisioner->provision([
            'subdomain' => 'agency-' . Str::random(6),
            'name' => 'Property Agency',
        ])->tenant();

        $this->assertNotNull($tenant, 'Tenant was not provisioned.');

        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $user = $userModel::create([
            'name' => 'Viewer',
            'email' => $email,
            'password' => bcrypt('password'),
        ]);
        $user->syncRoles([]);
        tenancy()->end();

        $domain = $tenant->domains()->first()->domain;
        $this->useTenantDomain($domain);

        $this->actingAs($user)
            ->get('http://' . $domain . route('properties.create', [], false))
            ->assertForbidden();
    }

    public function test_property_create_forbidden_for_non_property_manager_role(): void
    {
        $tenantProvisioner = app(TenantProvisioner::class);
        $email = 'viewer-role@example.com';

        $tenant = $tenantProvisioner->provision([
            'subdomain' => 'agency-' . Str::random(6),
            'name' => 'Property Agency',
        ])->tenant();

        $this->assertNotNull($tenant, 'Tenant was not provisioned.');

        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $user = $userModel::create([
            'name' => 'Viewer',
            'email' => $email,
            'password' => bcrypt('password'),
        ]);
        $guard = RolePermissionConfig::guard();
        Role::query()->firstOrCreate(['name' => 'viewer', 'guard_name' => $guard]);
        $user->syncRoles(['viewer']);
        tenancy()->end();

        $domain = $tenant->domains()->first()->domain;
        $this->useTenantDomain($domain);

        $this->actingAs($user)
            ->get('http://' . $domain . route('properties.create', [], false))
            ->assertForbidden();
    }

    public function test_property_routes_accessible_for_non_admin_property_manager_roles(): void
    {
        $tenantProvisioner = app(TenantProvisioner::class);
        $email = 'owner@example.com';

        $tenant = $tenantProvisioner->provision([
            'subdomain' => 'agency-' . Str::random(6),
            'name' => 'Property Agency',
            'user' => [
                'name' => 'Owner User',
                'email' => $email,
                'password' => 'password',
            ],
        ])->tenant();

        $this->assertNotNull($tenant, 'Tenant was not provisioned.');

        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $email)->firstOrFail();

        $propertyManagerRole = collect(config('roles.property_manager_roles'))
            ->first(fn (string $role): bool => $role !== AgencyRoles::tenantOwnerRole());

        $this->assertNotNull($propertyManagerRole, 'No property manager role available for assignment.');

        $user->syncRoles([$propertyManagerRole]);
        tenancy()->end();

        $domain = $tenant->domains()->first()->domain;
        $this->useTenantDomain($domain);

        $this->actingAs($user)
            ->get('http://' . $domain . route('properties.index', [], false))
            ->assertOk();
    }
}
