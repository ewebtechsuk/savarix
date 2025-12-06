<?php

namespace Tests\Concerns;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait UsesTenantSqliteDatabase
{
    protected function useTenantDatabase(Tenant $tenant): string
    {
        $tenantDatabase = database_path($tenant->database()->getName());
        config(['database.connections.sqlite.database' => $tenantDatabase]);
        DB::purge('sqlite');

        return $tenantDatabase;
    }

    protected function resetTenantDatabase(string $originalDatabase): void
    {
        config(['database.connections.sqlite.database' => $originalDatabase]);
        DB::purge('sqlite');
    }

    protected function ensureTenantSchema(): void
    {
        if (! $this->tenantSchemaIsComplete()) {
            $migration = require base_path('database/migrations/tenant/2026_09_30_000003_ensure_contact_and_property_media_columns.php');
            $migration->up();
        }
    }

    private function tenantSchemaIsComplete(): bool
    {
        return Schema::hasTable('contacts')
            && Schema::hasTable('properties')
            && Schema::hasTable('tenancies')
            && Schema::hasTable('invoices')
            && Schema::hasTable('payments');
    }
}
