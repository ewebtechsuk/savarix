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
        [$tenant, $user, $domain] = $this->provisionTenantUser('manager@example.com');

        $this->runInTenant($tenant, function () use ($user) {
            $user->syncRoles([AgencyRoles::tenantOwnerRole()]);
        });

        $this->assertTenantRouteOk($user, $domain, 'properties.create');
    }

    public function test_property_create_accessible_for_agency_admin_role(): void
    {
        [$tenant, $user, $domain] = $this->provisionTenantUser('agency-admin@example.com');

        $this->runInTenant($tenant, function () use ($user) {
            $guard = RolePermissionConfig::guard();
            Role::query()->firstOrCreate(['name' => 'agency_admin', 'guard_name' => $guard]);
            $user->syncRoles(['agency_admin']);
        });

        $this->assertTenantRouteOk($user, $domain, 'properties.create');
    }

    public function test_property_create_forbidden_for_users_without_roles(): void
    {
        [$tenant, $user, $domain] = $this->createTenantUserWithoutRoles('viewer@example.com');

        $this->assertTenantRouteForbidden($user, $domain, 'properties.create');
    }

    public function test_property_create_forbidden_for_non_property_manager_role(): void
    {
        [$tenant, $user, $domain] = $this->createTenantUserWithoutRoles('viewer-role@example.com');

        $this->runInTenant($tenant, function () use ($user) {
            $guard = RolePermissionConfig::guard();
            Role::query()->firstOrCreate(['name' => 'viewer', 'guard_name' => $guard]);
            $user->syncRoles(['viewer']);
        });

        $this->assertTenantRouteForbidden($user, $domain, 'properties.create');
    }

    public function test_property_routes_accessible_for_non_admin_property_manager_roles(): void
    {
        [$tenant, $user, $domain] = $this->provisionTenantUser('owner@example.com');

        $propertyManagerRole = collect(config('roles.property_manager_roles'))
            ->first(fn (string $role): bool => $role !== AgencyRoles::tenantOwnerRole());

        $this->assertNotNull($propertyManagerRole, 'No property manager role available for assignment.');

        $this->runInTenant($tenant, function () use ($user, $propertyManagerRole) {
            $user->syncRoles([$propertyManagerRole]);
        });

        $this->assertTenantRouteOk($user, $domain, 'properties.index');
    }

    public function test_contacts_routes_accessible_for_property_manager_roles(): void
    {
        [$tenant, $user, $domain] = $this->provisionTenantUser('contacts-manager@example.com');

        $propertyManagerRole = collect(config('roles.property_manager_roles'))
            ->first(fn (string $role): bool => $role !== AgencyRoles::tenantOwnerRole());

        $this->assertNotNull($propertyManagerRole, 'No property manager role available for assignment.');

        $this->runInTenant($tenant, function () use ($user, $propertyManagerRole) {
            $user->syncRoles([$propertyManagerRole]);
        });

        $this->assertTenantRouteOk($user, $domain, 'contacts.index');
        $this->assertTenantRouteOk($user, $domain, 'contacts.create');
    }

    public function test_contacts_routes_forbidden_for_users_without_roles(): void
    {
        [$tenant, $user, $domain] = $this->createTenantUserWithoutRoles('contacts-viewer@example.com');

        $this->assertTenantRouteForbidden($user, $domain, 'contacts.index');
        $this->assertTenantRouteForbidden($user, $domain, 'contacts.create');
    }

    public function test_property_and_contact_routes_forbidden_for_non_property_manager_role(): void
    {
        [$tenant, $user, $domain] = $this->createTenantUserWithoutRoles('contacts-viewer-role@example.com');

        $this->runInTenant($tenant, function () use ($user) {
            $guard = RolePermissionConfig::guard();
            Role::query()->firstOrCreate(['name' => 'viewer', 'guard_name' => $guard]);
            $user->syncRoles(['viewer']);
        });

        $this->assertTenantRouteForbidden($user, $domain, 'properties.create');
        $this->assertTenantRouteForbidden($user, $domain, 'contacts.index');
        $this->assertTenantRouteForbidden($user, $domain, 'contacts.create');
    }

    public function test_agency_admin_can_access_property_and_contacts_routes(): void
    {
        [$tenant, $user, $domain] = $this->provisionTenantUser('agency-admin-only@example.com');

        $this->runInTenant($tenant, function () use ($user) {
            $guard = RolePermissionConfig::guard();
            Role::query()->firstOrCreate(['name' => 'agency_admin', 'guard_name' => $guard]);
            $user->syncRoles(['agency_admin']);
        });

        $this->assertTenantRouteOk($user, $domain, 'properties.create');
        $this->assertTenantRouteOk($user, $domain, 'contacts.index');
        $this->assertTenantRouteOk($user, $domain, 'contacts.create');
    }

    private function provisionTenantUser(string $email): array
    {
        $tenantProvisioner = app(TenantProvisioner::class);

        $tenant = $tenantProvisioner->provision([
            'subdomain' => 'agency-' . Str::random(6),
            'name' => 'Property Agency',
            'user' => [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'password',
            ],
        ])->tenant();

        $this->assertNotNull($tenant, 'Tenant was not provisioned.');

        $domain = $tenant->domains()->first()->domain;

        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $email)->firstOrFail();
        tenancy()->end();

        $this->useTenantDomain($domain);

        return [$tenant, $user, $domain];
    }

    private function createTenantUserWithoutRoles(string $email): array
    {
        $tenantProvisioner = app(TenantProvisioner::class);

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
        tenancy()->end();

        $domain = $tenant->domains()->first()->domain;
        $this->useTenantDomain($domain);

        return [$tenant, $user, $domain];
    }

    private function runInTenant($tenant, callable $callback): void
    {
        tenancy()->initialize($tenant);

        try {
            $callback();
        } finally {
            tenancy()->end();
        }
    }

    private function assertTenantRouteOk($user, string $domain, string $routeName): void
    {
        $this->actingAs($user)
            ->get('http://' . $domain . route($routeName, [], false))
            ->assertOk();
    }

    private function assertTenantRouteForbidden($user, string $domain, string $routeName): void
    {
        $this->actingAs($user)
            ->get('http://' . $domain . route($routeName, [], false))
            ->assertForbidden();
    }
}
