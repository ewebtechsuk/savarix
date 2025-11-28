<?php

namespace App\Console\Commands\Savarix;

use App\Services\TenancyHealthReporter;
use Illuminate\Console\Command;

class TenancyHealthCheck extends Command
{
    protected $signature = 'savarix:tenancy-health {--host= : Optional host to check, e.g. aktonz.savarix.com}';

    protected $description = 'Inspect tenancy configuration and optionally validate a host domain.';

    public function handle(TenancyHealthReporter $reporter): int
    {
        $summary = $reporter->summary();

        $this->line('APP_URL: ' . ($summary['app_url'] ?: '(not set)'));
        $this->line('Central domains: ' . implode(', ', $summary['central_domains']));
        $this->line('Tenants count: ' . $summary['tenants_count']);
        $this->line('Domains count: ' . $summary['domains_count']);

        $exitCode = self::SUCCESS;
        $host = $this->option('host');

        if (is_string($host) && $host !== '') {
            $hostCheck = $reporter->inspectHost($host);

            $this->line('Host: ' . $hostCheck['host']);
            $this->line('Found: ' . ($hostCheck['found'] ? 'yes' : 'no'));
            $this->line('Tenant ID: ' . ($hostCheck['tenant_id'] ?? '—'));
            $this->line('Status: ' . $hostCheck['status']);

            if (! $hostCheck['found'] || $hostCheck['tenant_id'] === null) {
                $exitCode = self::FAILURE;
            }
        }

        return $exitCode;
    }
}
