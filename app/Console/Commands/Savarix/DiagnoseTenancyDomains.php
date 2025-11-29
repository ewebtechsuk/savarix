<?php

namespace App\Console\Commands\Savarix;

use App\Models\Agency;
use App\Models\Tenant;
use App\Services\TenantDomainSynchronizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Database\Models\Domain;
use Spatie\Permission\PermissionRegistrar;

class DiagnoseTenancyDomains extends Command
{
    protected $signature = 'savarix:diagnose-tenancy-domains {--sync : Ensure Tenant and Domain records exist for agencies with domains} {--seed-roles : Seed tenant roles and permissions for all tenants}';

    protected $description = 'Inspect tenancy domain configuration and optionally sync agency domains to tenants.';

    public function handle(TenantDomainSynchronizer $synchronizer): int
    {
        $this->line('App URL: ' . (config('app.url') ?: '(not set)'));
        $centralDomains = config('tenancy.central_domains', []);
        $this->line('Central domains: ' . implode(', ', array_map('strval', $centralDomains)));

        $this->line('Registered domains:');
        $domains = Domain::query()->orderBy('domain')->get(['id', 'tenant_id', 'domain']);
        $this->table(['ID', 'Tenant', 'Domain'], $domains->map(fn ($domain) => [
            $domain->id,
            $domain->tenant_id,
            $domain->domain,
        ])->all());

        $this->line('Agency → Tenant → Domains');
        $agencyRows = [];

        foreach (Agency::query()->orderBy('id')->get() as $agency) {
            $tenantId = $synchronizer->findTenantIdForAgency($agency);
            $tenant = $tenantId ? Tenant::query()->find($tenantId) : null;
            $tenantDomains = $tenant?->domains()->pluck('domain')->all() ?? [];

            $agencyRows[] = [
                'Agency' => sprintf('%s (#%d)', $agency->name, $agency->id),
                'Domain' => $agency->domain ?? '—',
                'Tenant' => $tenant?->getKey() ?? '—',
                'Tenant Domains' => $tenantDomains === [] ? '—' : implode(', ', $tenantDomains),
            ];
        }

        $this->table(['Agency', 'Domain', 'Tenant', 'Tenant Domains'], $agencyRows);

        if ($this->option('sync')) {
            $this->syncAgencies($synchronizer);
        }

        if ($this->option('seed-roles')) {
            $this->seedRolesAndPermissions();
        }

        return self::SUCCESS;
    }

    protected function syncAgencies(TenantDomainSynchronizer $synchronizer): void
    {
        $this->line('Syncing agency domains to tenants...');

        foreach (Agency::query()->orderBy('id')->get() as $agency) {
            $domain = $agency->domain;

            if ($domain === null) {
                continue;
            }

            $tenant = $synchronizer->syncForAgency($agency);

            if ($tenant === null) {
                continue;
            }

            $message = sprintf(
                'Agency #%d (%s) synced to tenant %s',
                $agency->id,
                $agency->name,
                $tenant->getKey()
            );

            $this->info($message);
            Log::info($message);
        }
    }

    protected function seedRolesAndPermissions(): void
    {
        foreach (Tenant::query()->orderBy('id')->get() as $tenant) {
            $this->line(sprintf('Seeding roles for tenant %s...', $tenant->getKey()));

            tenancy()->initialize($tenant);

            try {
                app(PermissionRegistrar::class)->forgetCachedPermissions();

                $exitCode = Artisan::call('db:seed', [
                    '--class' => \Database\Seeders\Tenancy\TenantRolesAndPermissionsSeeder::class,
                    '--force' => true,
                ]);

                if ($exitCode !== 0) {
                    $this->warn(sprintf('Seeder exited with code %d for tenant %s', $exitCode, $tenant->getKey()));
                } else {
                    $this->info(sprintf('Roles synced for tenant %s', $tenant->getKey()));
                }
            } finally {
                tenancy()->end();
            }
        }
    }
}
