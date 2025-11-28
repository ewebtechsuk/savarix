<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Stancl\Tenancy\Database\Models\Domain;
use Tests\TestCase;

class TenancyDebugRouteTest extends TestCase
{
    public function test_tenancy_debug_returns_tenant_context(): void
    {
        $this->useTenantDomain();

        $tenant = Tenant::create(['id' => 'aktonz']);
        Domain::create([
            'domain' => 'aktonz.savarix.com',
            'tenant_id' => $tenant->getKey(),
        ]);

        $user = User::factory()->create([
            'email' => 'tenant@example.com',
            'role' => 'Tenant',
        ]);

        Auth::login($user);

        $response = $this->withServerVariables([
            'HTTP_HOST' => 'aktonz.savarix.com',
            'SERVER_NAME' => 'aktonz.savarix.com',
        ])->getJson('http://aktonz.savarix.com/__tenancy-debug');

        $response->assertOk();
        $response->assertJsonFragment([
            'host' => 'aktonz.savarix.com',
            'tenancy_initialized' => true,
            'tenant_id' => 'aktonz',
        ]);
    }
}
