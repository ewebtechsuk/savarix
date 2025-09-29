<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Middleware\RoleMiddleware;
use Stancl\Tenancy\Database\Models\Domain;
use Tests\TestCase;

class TenantControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_rejects_existing_subdomain_variants(): void
    {
        $tenant = Tenant::factory()->create([
            'id' => 'existing',
            'slug' => 'existing',
            'name' => 'Existing Tenant',
        ]);

        $provisioner = $this->app->make(TenantProvisioner::class);

        Domain::create([
            'domain' => $provisioner->buildTenantDomain('existing'),
            'tenant_id' => $tenant->id,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->withoutMiddleware([
            RoleMiddleware::class,
            VerifyCsrfToken::class,
        ]);

        $response = $this->from(route('tenants.create'))
            ->post(route('tenants.store'), [
                'subdomain' => ' Existing . ',
            ]);

        $response->assertRedirect(route('tenants.create'));
        $response->assertSessionHasErrors('subdomain');
    }

    public function test_it_rejects_subdomains_with_invalid_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->withoutMiddleware([
            RoleMiddleware::class,
            VerifyCsrfToken::class,
        ]);

        $response = $this->from(route('tenants.create'))
            ->post(route('tenants.store'), [
                'subdomain' => 'invalid!',
            ]);

        $response->assertRedirect(route('tenants.create'));
        $response->assertSessionHasErrors([
            'subdomain' => 'The subdomain may only contain letters, numbers, and hyphens.',
        ]);
    }
}

