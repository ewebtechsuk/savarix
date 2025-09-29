<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Middleware\RoleMiddleware;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class InspectionAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('properties');

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        Schema::create('properties', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('inspections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('property_id')->nullable();
            $table->unsignedBigInteger('agent_id');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function test_tenant_user_can_view_tenant_inspections_index(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withoutMiddleware([
                InitializeTenancyByDomain::class,
                RoleMiddleware::class,
            ])
            ->actingAs($user)
            ->get('/inspections');

        $response->assertOk();
    }

    public function test_agent_user_can_view_agent_inspections_index(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withoutMiddleware([
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
                RoleMiddleware::class,
            ])
            ->actingAs($user)
            ->get('/agent/inspections');

        $response->assertOk();
    }

    public function test_agent_inspection_route_has_agent_prefix_and_middleware(): void
    {
        $route = app('router')->getRoutes()->getByName('agent.inspections.index');

        $this->assertNotNull($route);
        $this->assertSame('agent/inspections', $route->uri());

        $middleware = $route->gatherMiddleware();

        $this->assertTrue(
            $this->middlewareContains($middleware, InitializeTenancyByDomain::class, 'tenancy'),
            'Agent route should include tenancy middleware.'
        );
        $this->assertTrue(
            $this->middlewareContains($middleware, PreventAccessFromCentralDomains::class, 'preventAccessFromCentralDomains'),
            'Agent route should block central domains.'
        );
        $this->assertContains('role:Agent', $middleware, 'Agent route should enforce agent role.');
    }

    public function test_tenant_inspection_route_still_uses_tenant_middleware(): void
    {
        $route = app('router')->getRoutes()->getByName('inspections.index');

        $this->assertNotNull($route);
        $this->assertSame('inspections', $route->uri());

        $middleware = $route->gatherMiddleware();

        $this->assertTrue(
            $this->middlewareContains($middleware, InitializeTenancyByDomain::class, 'tenancy'),
            'Tenant route should include tenancy middleware.'
        );
        $this->assertContains('role:Tenant', $middleware, 'Tenant route should enforce tenant role.');
    }

    private function middlewareContains(array $middleware, string $class, string $alias): bool
    {
        return in_array($class, $middleware, true) || in_array($alias, $middleware, true);
    }
}
