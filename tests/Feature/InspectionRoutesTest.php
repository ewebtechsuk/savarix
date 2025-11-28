<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Middleware\RoleMiddleware;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class InspectionRoutesTest extends TestCase
{
    use DatabaseTransactions;

    private function bypassTenantRouteMiddleware(): void
    {
        $this->withoutMiddleware([
            RoleMiddleware::class,
            PreventAccessFromCentralDomains::class,
            InitializeTenancyByDomain::class,
        ]);
    }

    public function test_tenant_user_can_access_inspections_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->bypassTenantRouteMiddleware();
        $this->useTenantDomain();

        $response = $this->get(route('inspections.index'));

        $response->assertOk();
        $response->assertSee('Inspections');
    }

    public function test_agent_user_can_access_agent_inspections_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->bypassTenantRouteMiddleware();
        $this->useTenantDomain();

        $response = $this->get(route('agent.inspections.index'));

        $response->assertOk();
        $response->assertSee('Inspections');
    }
}
