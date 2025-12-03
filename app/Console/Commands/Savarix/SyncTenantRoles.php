<?php

declare(strict_types=1);

namespace App\Console\Commands\Savarix;

use App\Models\Tenant;
use App\Services\TenantRoleSynchronizer;
use Illuminate\Console\Command;

class SyncTenantRoles extends Command
{
    protected $signature = 'savarix:sync-tenant-roles {tenant? : Tenant ID (subdomain) to sync. Leave empty to sync all tenants.}';

    protected $description = 'Ensure required roles and permissions exist for one or all tenants.';

    public function handle(TenantRoleSynchronizer $synchronizer): int
    {
        $tenantId = $this->argument('tenant');

        $tenants = $tenantId !== null
            ? Tenant::query()->whereKey($tenantId)->get()
            : Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn($tenantId ? "Tenant {$tenantId} was not found." : 'No tenants to process.');

            return self::FAILURE;
        }

        $tenants->each(function (Tenant $tenant) use ($synchronizer) {
            $this->info("Syncing roles for tenant {$tenant->getKey()}...");
            $synchronizer->syncForTenant($tenant);
        });

        $this->info('Role and permission sync complete.');

        return self::SUCCESS;
    }
}
