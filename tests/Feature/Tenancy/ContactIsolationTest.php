<?php

namespace Tests\Feature\Tenancy;

use App\Models\Contact;
use App\Services\TenantProvisioner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\UsesTenantSqliteDatabase;
use Tests\TestCase;

class ContactIsolationTest extends TestCase
{
    use UsesTenantSqliteDatabase;

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

        tenancy()->initialize($firstTenant);

        try {
            $firstDatabaseName = $this->useTenantDatabase($firstTenant);
            $this->ensureTenantSchema();

            Contact::factory()->create([
                'name' => 'Contact for first tenant',
                'type' => 'tenant',
            ]);

            $this->assertSame(['Contact for first tenant'], Contact::pluck('name')->all());
        } finally {
            $this->resetTenantDatabase($originalDatabase);
            tenancy()->end();
        }

        tenancy()->initialize($secondTenant);

        try {
            $secondDatabaseName = $this->useTenantDatabase($secondTenant);
            $this->ensureTenantSchema();

            Contact::factory()->create([
                'name' => 'Contact for second tenant',
                'type' => 'tenant',
            ]);

            $this->assertSame(['Contact for second tenant'], Contact::pluck('name')->all());
        } finally {
            $this->resetTenantDatabase($originalDatabase);
            tenancy()->end();
        }

        $this->assertNotSame($firstDatabaseName, $secondDatabaseName, 'Tenants should use separate databases.');

        tenancy()->initialize($firstTenant);

        try {
            $this->assertSame($firstDatabaseName, $this->useTenantDatabase($firstTenant));
            $this->assertSame(['Contact for first tenant'], Contact::pluck('name')->all());
        } finally {
            $this->resetTenantDatabase($originalDatabase);
            tenancy()->end();
        }
    }
}
