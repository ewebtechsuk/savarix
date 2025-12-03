<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Support\AgencyRoles;
use App\Services\TenantRoleSynchronizer;
use Database\Seeders\RolePermissionConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class TenantGrantOwnerRole extends Command
{
    protected $signature = 'tenant:grant-owner {email : Email address of the tenant user} {--tenant= : Tenant ID to run the command against}';

    protected $description = 'Assign the tenant owner/admin role to a user inside a specific tenant database.';

    public function __construct(private readonly TenantRoleSynchronizer $tenantRoleSynchronizer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $email = (string) $this->argument('email');

        if (! $tenantId) {
            $this->error('Please provide a tenant id via --tenant.');

            return self::FAILURE;
        }

        $tenant = Tenant::find($tenantId);

        if ($tenant === null) {
            $this->error("Tenant {$tenantId} was not found.");

            return self::FAILURE;
        }

        tenancy()->initialize($tenant);

        try {
            $userModel = config('auth.providers.users.model');

            if (! is_string($userModel) || $userModel === '') {
                throw new \RuntimeException('User model is not configured.');
            }

            /** @var \App\Models\User|null $user */
            $user = $userModel::query()->where('email', $email)->first();

            if ($user === null) {
                $this->error("No user found with email {$email} in tenant {$tenantId}.");

                return self::FAILURE;
            }

            Log::info('tenant:grant-owner user', ['id' => $user->getKey(), 'email' => $user->email]);

            $this->ensureRolesAndPermissions();

            $roles = AgencyRoles::ownerAssignableRoles();
            $this->line('Roles to assign: ' . implode(', ', $roles));
            Log::info('tenant:grant-owner roles', ['tenant' => $tenantId, 'email' => $email, 'roles' => $roles]);
            $roleIds = Role::query()
                ->whereIn('name', $roles)
                ->where('guard_name', RolePermissionConfig::guard())
                ->pluck('id');
            Log::info('tenant:grant-owner role ids', ['ids' => $roleIds]);

            $connection = DB::connection(config('database.default'));
            $connection->table(config('permission.table_names.model_has_roles', 'model_has_roles'))
                ->where('model_type', $user->getMorphClass())
                ->where('model_id', $user->getKey())
                ->delete();

            foreach ($roleIds as $roleId) {
                $connection->table(config('permission.table_names.model_has_roles', 'model_has_roles'))->insert([
                    'role_id' => $roleId,
                    'model_type' => $user->getMorphClass(),
                    'model_id' => $user->getKey(),
                ]);
            }

            Log::info('tenant:grant-owner model_has_roles', [
                'count' => $connection->table(config('permission.table_names.model_has_roles', 'model_has_roles'))
                    ->where('model_id', $user->getKey())
                    ->count(),
            ]);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $this->line('Current roles: ' . $user->getRoleNames()->implode(', '));

            $this->info(sprintf('Assigned roles [%s] to %s in tenant %s.', implode(', ', $roles), $email, $tenantId));

            return self::SUCCESS;
            } catch (Throwable $exception) {
            Log::error('Failed to grant owner role to tenant user.', [
                'tenant_id' => $tenantId,
                'email' => $email,
                'exception' => $exception,
            ]);

            $this->error('Unable to assign roles: ' . $exception->getMessage());

            return self::FAILURE;
            } finally {
                tenancy()->end();
            }
    }

    protected function ensureRolesAndPermissions(): void
    {
        $this->tenantRoleSynchronizer->syncInCurrentTenant();
    }
}
