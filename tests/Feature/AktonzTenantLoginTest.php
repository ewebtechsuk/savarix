<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AktonzTenantLoginTest extends TestCase
{
    public function test_tenant_subdomain_does_not_expose_default_login(): void
    {
        Tenant::factory()->create([
            'id' => 'aktonz',
            'name' => 'Aktonz Estate Agents',
            'data' => [
                'slug' => 'aktonz',
                'company_name' => 'Aktonz Estate Agents',
                'company_email' => 'info@aktonz.com',
                'company_id' => '468173',
                'domains' => ['aktonz.savarix.com'],
            ],
        ])->domains()->create(['domain' => 'aktonz.savarix.com']);

        $response = $this->withServerVariables([
            'HTTP_HOST' => 'aktonz.savarix.com',
        ])->get('https://aktonz.savarix.com/login');

        $response->assertNotFound();
    }
}
