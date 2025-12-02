<?php

namespace Tests\Feature\Console;

use App\Services\TenantProvisioner;
use App\Support\AgencyRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class TenantGrantOwnerCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_assigns_owner_role(): void
    {
        $provisioner = app(TenantProvisioner::class);
        $tenant = $provisioner->provision([
            'subdomain' => 'agency-' . Str::random(6),
            'name' => 'Role Fix Agency',
        ])->tenant();

        $this->assertNotNull($tenant, 'Tenant could not be provisioned');

        tenancy()->initialize($tenant);
        $userModel = config('auth.providers.users.model');
        $user = $userModel::create([
            'name' => 'Needs Roles',
            'email' => 'needs-roles@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->syncRoles([]);
        tenancy()->end();

        tenancy()->initialize($tenant);
        $exitCode = Artisan::call('tenant:grant-owner', [
            'email' => 'needs-roles@example.com',
            '--tenant' => $tenant->id,
        ]);
        tenancy()->end();

        $this->assertSame(0, $exitCode);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $user = $userModel::find($user->id);
        $this->assertNotEmpty(Role::pluck('name')->all());
        $user->load('roles');
        $this->assertNotEmpty($user->roles);
        $this->assertGreaterThan(
            0,
            DB::table('model_has_roles')->where('model_id', $user->id)->count()
        );
        $this->assertSame(
            collect(AgencyRoles::ownerAssignableRoles())->sort()->values()->all(),
            $user->getRoleNames()->sort()->values()->all()
        );
        tenancy()->end();
    }
}
