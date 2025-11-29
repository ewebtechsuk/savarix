<?php

namespace Tests\Feature\Console;

use App\Models\Agency;
use App\Models\Tenant;
use App\Services\TenantProvisioner;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Database\Models\Domain;
use Tests\TestCase;

class DiagnoseTenancyDomainsTest extends TestCase
{
    public function test_it_syncs_agency_domains_to_tenants(): void
    {
        config()->set('app.url', 'https://savarix.com');
        config()->set('tenancy.central_domains', ['savarix.com', '127.0.0.1', 'localhost']);

        $agency = Agency::create([
            'name' => 'Aktonz',
            'slug' => 'aktonz',
            'domain' => 'https://aktonz.savarix.com',
        ]);

        // Remove any automatically synced tenant/domain to assert the command can recreate them.
        Tenant::query()->delete();
        Domain::query()->delete();

        $this->assertDatabaseMissing('tenants', ['id' => 'aktonz']);
        $this->assertDatabaseMissing('domains', ['domain' => 'aktonz.savarix.com']);

        Artisan::call('savarix:diagnose-tenancy-domains', ['--sync' => true]);

        $this->assertDatabaseHas('tenants', ['id' => 'aktonz']);
        $this->assertDatabaseHas('domains', [
            'domain' => 'aktonz.savarix.com',
            'tenant_id' => 'aktonz',
        ]);

        $agency->refresh();
        $this->assertNotNull($agency->domain);
    }

    public function test_it_seeds_roles_for_each_tenant(): void
    {
        $provisioner = app(TenantProvisioner::class);

        $result = $provisioner->provision([
            'subdomain' => 'seed-tenant',
            'name' => 'Seed Tenant',
        ]);

        $tenant = $result->tenant();
        $this->assertNotNull($tenant);

        tenancy()->initialize($tenant);

        try {
            // Remove permissions to verify reseeding works
            \Spatie\Permission\Models\Permission::query()->delete();
        } finally {
            tenancy()->end();
        }

        Artisan::call('savarix:diagnose-tenancy-domains', ['--seed-roles' => true]);

        tenancy()->initialize($tenant);

        try {
            $this->assertDatabaseHas('permissions', ['name' => 'properties.view']);
            $this->assertDatabaseHas('roles', ['name' => 'Admin']);
        } finally {
            tenancy()->end();
        }
    }
}
