<?php

namespace Tests\Feature\Tenancy;

use App\Models\Property;
use App\Services\TenantProvisioner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\UsesTenantSqliteDatabase;
use Tests\TestCase;

class PropertyIsolationTest extends TestCase
{
    use UsesTenantSqliteDatabase;

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

        tenancy()->initialize($firstTenant);

        try {
            $firstDatabaseName = $this->useTenantDatabase($firstTenant);
            $this->ensureTenantSchema();

            Property::factory()->create([
                'title' => 'Property for first tenant',
            ]);

            $this->assertSame(['Property for first tenant'], Property::pluck('title')->all());
        } finally {
            $this->resetTenantDatabase($originalDatabase);
            tenancy()->end();
        }

        tenancy()->initialize($secondTenant);

        try {
            $secondDatabaseName = $this->useTenantDatabase($secondTenant);
            $this->ensureTenantSchema();

            Property::factory()->create([
                'title' => 'Property for second tenant',
            ]);

            $this->assertSame(['Property for second tenant'], Property::pluck('title')->all());
        } finally {
            $this->resetTenantDatabase($originalDatabase);
            tenancy()->end();
        }

        $this->assertNotSame($firstDatabaseName, $secondDatabaseName, 'Tenants should use separate databases.');

        tenancy()->initialize($firstTenant);

        try {
            $this->assertSame($firstDatabaseName, $this->useTenantDatabase($firstTenant));
            $this->assertSame(['Property for first tenant'], Property::pluck('title')->all());
        } finally {
            $this->resetTenantDatabase($originalDatabase);
            tenancy()->end();
        }
    }
}
