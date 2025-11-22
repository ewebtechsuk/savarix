<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Agency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckAgencyImpersonation extends Command
{
    protected $signature = 'savarix:check-impersonation {agency : Agency ID or slug}';

    protected $description = 'Verify agency domain, admin user, and session config needed for impersonation and tenant redirects.';

    public function handle(): int
    {
        $identifier = $this->argument('agency');

        $agency = Agency::query()
            ->when(is_numeric($identifier), fn ($query) => $query->where('id', (int) $identifier))
            ->when(! is_numeric($identifier), fn ($query) => $query->orWhere('slug', (string) $identifier))
            ->first();

        if (! $agency) {
            $this->error('Agency not found.');

            return self::FAILURE;
        }

        $this->info("Agency: {$agency->name} (ID: {$agency->id})");
        $this->line('Domain: ' . ($agency->domain ?? 'not set'));

        $schema = Schema::connection(config('tenancy.database.central_connection', config('database.default')));
        $this->line('agencies.domain column present: ' . ($schema->hasColumn('agencies', 'domain') ? 'yes' : 'no'));

        $dashboardUrl = $agency->tenantDashboardUrl();
        $this->line('Tenant dashboard URL: ' . ($dashboardUrl ?? 'missing domain'));

        $admin = $agency->users()
            ->where('role', 'agency_admin')
            ->orderBy('id')
            ->first();

        if ($admin) {
            $this->info('Agency admin found: ' . $admin->email . ' (user ID: ' . $admin->id . ')');
        } else {
            $this->warn('No agency_admin user found for this agency.');
        }

        $this->line('SESSION_DOMAIN: ' . (config('session.domain') ?? 'not set'));
        $this->line('SESSION_SECURE_COOKIE: ' . (config('session.secure') ? 'true' : 'false'));
        $this->line('SESSION_SAME_SITE: ' . (config('session.same_site') ?? 'not set'));

        return self::SUCCESS;
    }
}
