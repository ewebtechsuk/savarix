<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioner;
use App\Support\SubdomainNormalizer;
use Illuminate\Support\Facades\DB;
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

        $variant = ' Existing . ';

        $response = $this->from(route('tenants.create'))
            ->post(route('tenants.store'), [
                'subdomain' => $variant,
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

    public function test_update_rejects_subdomains_with_invalid_characters(): void
    {
        $tenant = Tenant::factory()->create([
            'id' => 'tenant-one',
            'slug' => 'tenant-one',
            'name' => 'Tenant One',
        ]);

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->withoutMiddleware([
            RoleMiddleware::class,
            VerifyCsrfToken::class,
        ]);

        $response = $this->from(route('tenants.edit', $tenant->id))
            ->put(route('tenants.update', $tenant->id), [
                'subdomain' => 'invalid!',
            ]);

        $response->assertRedirect(route('tenants.edit', $tenant->id));
        $response->assertSessionHasErrors([
            'subdomain' => 'The subdomain may only contain letters, numbers, and hyphens.',
        ]);
    }

    public function test_update_rejects_existing_subdomain_variants(): void
    {
        $existingTenant = Tenant::factory()->create([
            'id' => 'existing',
            'slug' => 'existing',
            'name' => 'Existing Tenant',
        ]);

        $targetTenant = Tenant::factory()->create([
            'id' => 'target',
            'slug' => 'target',
            'name' => 'Target Tenant',
        ]);

        $provisioner = $this->app->make(TenantProvisioner::class);

        Domain::create([
            'domain' => $provisioner->buildTenantDomain('existing'),
            'tenant_id' => $existingTenant->id,
        ]);

        Domain::create([
            'domain' => $provisioner->buildTenantDomain('target'),
            'tenant_id' => $targetTenant->id,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->withoutMiddleware([
            RoleMiddleware::class,
            VerifyCsrfToken::class,
        ]);

        $variant = ' EXISTING . ';

        $response = $this->from(route('tenants.edit', $targetTenant->id))
            ->put(route('tenants.update', $targetTenant->id), [
                'subdomain' => $variant,
            ]);

        $response->assertRedirect(route('tenants.edit', $targetTenant->id));
        $response->assertSessionHasErrors('subdomain');
        $this->assertSame('existing', SubdomainNormalizer::normalize($variant));
        $this->assertTrue(DB::table('domains')->where('domain', $provisioner->buildTenantDomain(SubdomainNormalizer::normalize($variant)))->exists());
    }
}

