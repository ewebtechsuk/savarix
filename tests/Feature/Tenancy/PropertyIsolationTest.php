<?php

namespace Tests\Feature\Tenancy;

use App\Models\Property;
use App\Services\TenantProvisioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PropertyIsolationTest extends TestCase
{
    public function test_properties_are_isolated_by_tenant_database(): void
    {
        $provisioner = app(TenantProvisioner::class);

        config(['database.connections.tenant' => config('database.connections.sqlite')]);

        $firstTenant = $provisioner
            ->provision([
                'subdomain' => 'properties-a-' . Str::random(6),
                'name' => 'Properties A',
            ])
            ->tenant();

        $secondTenant = $provisioner
            ->provision([
                'subdomain' => 'properties-b-' . Str::random(6),
                'name' => 'Properties B',
            ])
            ->tenant();

        $this->assertNotNull($firstTenant, 'First tenant provisioning failed.');
        $this->assertNotNull($secondTenant, 'Second tenant provisioning failed.');

        $originalDatabase = config('database.connections.sqlite.database');

        $useTenantDatabase = function ($tenant): string {
            $tenantDatabase = database_path($tenant->database()->getName());
            config(['database.connections.sqlite.database' => $tenantDatabase]);
            DB::purge('sqlite');

            return $tenantDatabase;
        };

        $resetDatabase = function () use ($originalDatabase): void {
            config(['database.connections.sqlite.database' => $originalDatabase]);
            DB::purge('sqlite');
        };

        tenancy()->initialize($firstTenant);

        try {
            $firstDatabaseName = $useTenantDatabase($firstTenant);

            if (! Schema::hasTable('properties')) {
                $migration = require base_path('database/migrations/tenant/2026_09_30_000003_ensure_contact_and_property_media_columns.php');
                $migration->up();
            }

            Property::factory()->create([
                'title' => 'Property for first tenant',
            ]);

            $this->assertSame(['Property for first tenant'], Property::pluck('title')->all());
        } finally {
            $resetDatabase();
            tenancy()->end();
        }

        tenancy()->initialize($secondTenant);

        try {
            $secondDatabaseName = $useTenantDatabase($secondTenant);

            if (! Schema::hasTable('properties')) {
                $migration = require base_path('database/migrations/tenant/2026_09_30_000003_ensure_contact_and_property_media_columns.php');
                $migration->up();
            }

            Property::factory()->create([
                'title' => 'Property for second tenant',
            ]);

            $this->assertSame(['Property for second tenant'], Property::pluck('title')->all());
        } finally {
            $resetDatabase();
            tenancy()->end();
        }

        $this->assertNotSame($firstDatabaseName, $secondDatabaseName, 'Tenants should use separate databases.');

        tenancy()->initialize($firstTenant);

        try {
            $this->assertSame($firstDatabaseName, $useTenantDatabase($firstTenant));
            $this->assertSame(['Property for first tenant'], Property::pluck('title')->all());
        } finally {
            $resetDatabase();
            tenancy()->end();
        }
    }
}
