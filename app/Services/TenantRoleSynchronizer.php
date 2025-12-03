<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use Database\Seeders\RolePermissionConfig;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class TenantRoleSynchronizer
{
    public function __construct(private readonly PermissionRegistrar $permissionRegistrar)
    {
    }

    public function syncForTenant(Tenant $tenant): void
    {
        tenancy()->initialize($tenant);

        try {
            $this->syncInCurrentTenant();
        } catch (Throwable $exception) {
            Log::warning('Failed to sync roles for tenant', [
                'tenant_id' => $tenant->getKey(),
                'exception' => $exception,
            ]);
        } finally {
            tenancy()->end();
        }
    }

    public function syncInCurrentTenant(): void
    {
        $this->permissionRegistrar->forgetCachedPermissions();

        $guard = RolePermissionConfig::guard();

        foreach (RolePermissionConfig::permissions() as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        foreach (RolePermissionConfig::roles() as $roleName) {
            $role = Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => $guard,
            ]);

            $role->syncPermissions(RolePermissionConfig::rolePermissions()[$roleName] ?? []);
        }
    }
}
