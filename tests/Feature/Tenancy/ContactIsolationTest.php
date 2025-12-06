<?php

namespace Tests\Feature\Tenancy;

use App\Models\Contact;
use App\Services\TenantProvisioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactIsolationTest extends TestCase
{
    public function test_contacts_are_isolated_by_tenant_database(): void
    {
        $provisioner = app(TenantProvisioner::class);

        config(['database.connections.tenant' => config('database.connections.sqlite')]);

        $firstTenant = $provisioner
            ->provision([
                'subdomain' => 'contacts-a-' . Str::random(6),
                'name' => 'Contacts A',
            ])
            ->tenant();

        $secondTenant = $provisioner
            ->provision([
                'subdomain' => 'contacts-b-' . Str::random(6),
                'name' => 'Contacts B',
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

            if (! Schema::hasTable('contacts')) {
                $migration = require base_path('database/migrations/tenant/2026_09_30_000003_ensure_contact_and_property_media_columns.php');
                $migration->up();
            }

            Contact::factory()->create([
                'name' => 'Contact for first tenant',
                'type' => 'tenant',
            ]);

            $this->assertSame(['Contact for first tenant'], Contact::pluck('name')->all());
        } finally {
            $resetDatabase();
            tenancy()->end();
        }

        tenancy()->initialize($secondTenant);

        try {
            $secondDatabaseName = $useTenantDatabase($secondTenant);

            if (! Schema::hasTable('contacts')) {
                $migration = require base_path('database/migrations/tenant/2026_09_30_000003_ensure_contact_and_property_media_columns.php');
                $migration->up();
            }

            Contact::factory()->create([
                'name' => 'Contact for second tenant',
                'type' => 'tenant',
            ]);

            $this->assertSame(['Contact for second tenant'], Contact::pluck('name')->all());
        } finally {
            $resetDatabase();
            tenancy()->end();
        }

        $this->assertNotSame($firstDatabaseName, $secondDatabaseName, 'Tenants should use separate databases.');

        tenancy()->initialize($firstTenant);

        try {
            $this->assertSame($firstDatabaseName, $useTenantDatabase($firstTenant));
            $this->assertSame(['Contact for first tenant'], Contact::pluck('name')->all());
        } finally {
            $resetDatabase();
            tenancy()->end();
        }
    }
}
