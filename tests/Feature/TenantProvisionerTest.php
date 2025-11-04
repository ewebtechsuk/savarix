<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Services\TenantProvisioner;
use App\Services\TenantProvisioningResult;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

class TenantProvisionerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_provisions_a_tenant_with_domain_and_seeded_users(): void
    {
        $service = $this->app->make(TenantProvisioner::class);

        $result = $service->provision([
            'subdomain' => 'acme',
            'data' => ['plan' => 'pro'],
            'name' => 'Acme Inc.',
        ]);

        $this->assertSame(TenantProvisioningResult::STATUS_SUCCESS, $result->status());
        $this->assertNotNull($result->tenant());

        $tenant = Tenant::findOrFail('acme');
        $domain = $tenant->domains()->first();
        $this->assertNotNull($domain);
        $this->assertSame($service->buildTenantDomain('acme'), $domain->domain);

        tenancy()->initialize($tenant);

        try {
            $this->assertDatabaseHas('users', ['email' => 'admin@savirix.com']);
            $this->assertDatabaseHas('users', ['email' => 'tenant@aktonz.com']);
        } finally {
            tenancy()->end();
        }
    }

    public function test_it_marks_partial_failure_when_initial_user_creation_fails(): void
    {
        $service = $this->app->make(TenantProvisioner::class);

        $result = $service->provision([
            'subdomain' => 'beta',
            'user' => [
                'name' => 'Duplicate User',
                'email' => 'tenant@aktonz.com',
                'password' => 'secret123',
            ],
        ]);

        $this->assertSame(TenantProvisioningResult::STATUS_PARTIAL, $result->status());
        $this->assertNotEmpty($result->errors());
        $this->assertNotNull($result->tenant());

        $tenant = Tenant::findOrFail('beta');

        tenancy()->initialize($tenant);

        try {
            $this->assertDatabaseHas('users', ['email' => 'admin@savirix.com']);
        } finally {
            tenancy()->end();
        }
    }

    public function test_it_rolls_back_when_migrations_fail(): void
    {
        $service = $this->app->make(TenantProvisioner::class);

        Artisan::shouldReceive('call')
            ->once()
            ->with('migrate', Mockery::type('array'))
            ->andReturn(1);

        $result = $service->provision([
            'subdomain' => 'gamma',
        ]);

        $this->assertSame(TenantProvisioningResult::STATUS_ROLLED_BACK, $result->status());
        $this->assertNull($result->tenant());
        $this->assertNull(Tenant::find('gamma'));
    }
}
