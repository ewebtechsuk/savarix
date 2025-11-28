<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Middleware\RoleMiddleware;
use Stancl\Tenancy\Database\Models\Domain;
use Tests\TestCase;

class TenancyRoutingTest extends TestCase
{
    use DatabaseTransactions;

    private const CENTRAL_DOMAIN = 'savarix.com';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('tenancy.central_domains', [self::CENTRAL_DOMAIN]);
        Config::set('app.url', 'https://'.self::CENTRAL_DOMAIN);
    }

    public function test_central_domain_can_access_login(): void
    {
        $response = $this->get('http://'.self::CENTRAL_DOMAIN.'/login');

        $response->assertOk();
    }

    public function test_tenant_domain_gets_404_on_login(): void
    {
        $response = $this->get('http://tenant.'.self::CENTRAL_DOMAIN.'/login');

        $response->assertNotFound();
    }

    public function test_tenant_dashboard_uses_tenancy_stack(): void
    {
        $tenant = Tenant::factory()->create(['id' => 'agency-1']);

        Domain::create([
            'domain' => 'aktonz.'.self::CENTRAL_DOMAIN,
            'tenant_id' => $tenant->id,
        ]);

        $user = User::factory()->create();

        $this->withoutMiddleware(RoleMiddleware::class);

        $response = $this->actingAs($user)
            ->withServerVariables(['HTTP_HOST' => 'aktonz.'.self::CENTRAL_DOMAIN])
            ->get('/dashboard');

        $response->assertOk();
    }
}
