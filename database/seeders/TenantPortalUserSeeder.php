<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantPortalUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureAdminUser();
        $this->ensureTenantUser();
    }

    private function ensureAdminUser(): void
    {
        if (User::where('email', 'admin@savirix.com')->exists()) {
            return;
        }

        $user = User::factory()->create([
            'name' => 'Portal Admin',
            'email' => 'admin@savirix.com',
            'password' => Hash::make(env('PORTAL_ADMIN_PASSWORD', 'changeme')),
            'is_admin' => true,
        ]);

        $this->assignRolesIfPresent($user, ['Admin', 'Tenant']);
    }

    private function ensureTenantUser(): void
    {
        if (User::where('email', 'tenant@aktonz.com')->exists()) {
            return;
        }

        $user = User::factory()->create([
            'name' => 'Aktonz Tenant',
            'email' => 'tenant@aktonz.com',
            'password' => Hash::make(env('PORTAL_TENANT_PASSWORD', 'secret')),
            'is_admin' => false,
        ]);

        $this->assignRolesIfPresent($user, ['Tenant']);
    }

    /**
     * @param array<int, string> $roles
     */
    private function assignRolesIfPresent(User $user, array $roles): void
    {
        $guard = config('permission.defaults.guard', 'web');

        $assignable = collect($roles)
            ->filter(function (string $role) use ($guard) {
                return Role::query()->where('name', $role)->where('guard_name', $guard)->exists();
            })
            ->values()
            ->all();

        if ($assignable !== []) {
            $user->syncRoles($assignable);
        }
    }
}
