<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middleware\RoleMiddleware;
use Stancl\Tenancy\Database\Models\Domain;
use Tests\TestCase;

class TenancyHealthRouteTest extends TestCase
{
    public function test_health_route_returns_summary_and_host_check(): void
    {
        $this->withoutMiddleware(RoleMiddleware::class);

        config()->set('app.url', 'https://savarix.com');
        config()->set('tenancy.central_domains', ['savarix.com']);

        $tenant = Tenant::factory()->create(['id' => 'aktonz']);
        Domain::create([
            'domain' => 'aktonz.savarix.com',
            'tenant_id' => $tenant->getKey(),
        ]);

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'Admin',
        ]);

        $this->actingAs($admin);

        $response = $this->withServerVariables([
            'HTTP_HOST' => 'savarix.com',
            'SERVER_NAME' => 'savarix.com',
        ])
            ->getJson('http://savarix.com/__health/tenancy?host=aktonz.savarix.com');

        $response->assertOk();
        $response->assertJsonStructure([
            'app_url',
            'central_domains',
            'tenants_count',
            'domains_count',
            'host_check' => ['host', 'found', 'tenant_id', 'status'],
        ]);
    }
}
