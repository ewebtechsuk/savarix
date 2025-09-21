<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class ListTenants extends Command
{
    protected $signature = 'tenant:list {--json : Output tenant details as JSON}';

    protected $description = 'Display all registered tenants and their domains.';

    public function handle(): int
    {
        $tenants = Tenant::query()->with('domains')->orderBy('id')->get();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants have been registered yet.');

            return self::SUCCESS;
        }

        if ($this->option('json')) {
            $payload = $tenants->map(function (Tenant $tenant) {
                return [
                    'id' => $tenant->id,
                    'company_name' => $tenant->data['company_name'] ?? null,
                    'company_email' => $tenant->data['email'] ?? null,
                    'domains' => $tenant->domains->pluck('domain')->values()->all(),
                ];
            })->values();

            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $rows = $tenants->map(function (Tenant $tenant) {
            $domains = $tenant->domains->pluck('domain');

            return [
                'Tenant ID' => $tenant->id,
                'Company' => $tenant->data['company_name'] ?? '—',
                'Primary Domain' => $domains->first() ?? '—',
                'All Domains' => $domains->implode(PHP_EOL) ?: '—',
            ];
        })->values()->all();

        $this->table(
            ['Tenant ID', 'Company', 'Primary Domain', 'All Domains'],
            array_map(fn ($row) => array_values($row), $rows)
        );

        $this->info(sprintf('Total tenants: %d', $tenants->count()));

        return self::SUCCESS;
    }
}
