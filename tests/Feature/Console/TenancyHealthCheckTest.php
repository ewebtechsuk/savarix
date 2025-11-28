<?php

namespace Tests\Feature\Console;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Database\Models\Domain;
use Tests\TestCase;

class TenancyHealthCheckTest extends TestCase
{
    public function test_command_reports_success_when_domain_exists(): void
    {
        config()->set('tenancy.central_domains', ['savarix.com']);

        $tenant = Tenant::factory()->create(['id' => 'aktonz']);
        Domain::create([
            'domain' => 'aktonz.savarix.com',
            'tenant_id' => $tenant->getKey(),
        ]);

        $exitCode = Artisan::call('savarix:tenancy-health', ['--host' => 'aktonz.savarix.com']);

        $this->assertSame(0, $exitCode);
    }

    public function test_command_reports_failure_when_domain_is_missing(): void
    {
        config()->set('tenancy.central_domains', ['savarix.com']);

        $exitCode = Artisan::call('savarix:tenancy-health', ['--host' => 'missing.savarix.com']);

        $this->assertSame(1, $exitCode);
    }
}
