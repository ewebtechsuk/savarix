<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\CompanyController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioner;
use App\Support\SubdomainNormalizer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\RoleMiddleware;
use Stancl\Tenancy\Database\Models\Domain;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Route::has('admin.companies.store')) {
            Route::middleware('web')->group(function () {
                Route::post('admin/companies', [CompanyController::class, 'store'])
                    ->name('admin.companies.store');
                Route::put('admin/companies/{company}', [CompanyController::class, 'update'])
                    ->name('admin.companies.update');
            });
        }
    }

    public function test_store_rejects_subdomains_with_invalid_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->withoutMiddleware([
            RoleMiddleware::class,
            VerifyCsrfToken::class,
        ]);

        $response = $this->from('/admin/companies/create')
            ->post('/admin/companies', [
                'name' => 'Test Company',
                'subdomain' => 'invalid!',
            ]);

        $response->assertRedirect('/admin/companies/create');
        $response->assertSessionHasErrors([
            'subdomain' => 'The subdomain may only contain letters, numbers, and hyphens.',
        ]);
    }

    public function test_store_rejects_duplicate_subdomains(): void
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

        $response = $this->from('/admin/companies/create')
            ->post('/admin/companies', [
                'name' => 'Another Company',
                'subdomain' => $variant,
            ]);

        $response->assertRedirect('/admin/companies/create');
        $response->assertSessionHasErrors('subdomain');
        $this->assertSame('existing', SubdomainNormalizer::normalize($variant));
    }

    public function test_update_rejects_subdomains_with_invalid_characters(): void
    {
        $tenant = Tenant::factory()->create([
            'id' => 'company-one',
            'slug' => 'company-one',
            'name' => 'Company One',
        ]);

        $provisioner = $this->app->make(TenantProvisioner::class);

        Domain::create([
            'domain' => $provisioner->buildTenantDomain('company-one'),
            'tenant_id' => $tenant->id,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->withoutMiddleware([
            RoleMiddleware::class,
            VerifyCsrfToken::class,
        ]);

        $response = $this->from('/admin/companies/' . $tenant->id . '/edit')
            ->put('/admin/companies/' . $tenant->id, [
                'subdomain' => 'invalid!',
            ]);

        $response->assertRedirect('/admin/companies/' . $tenant->id . '/edit');
        $response->assertSessionHasErrors([
            'subdomain' => 'The subdomain may only contain letters, numbers, and hyphens.',
        ]);
    }

    public function test_update_rejects_duplicate_subdomains(): void
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

        $response = $this->from('/admin/companies/' . $targetTenant->id . '/edit')
            ->put('/admin/companies/' . $targetTenant->id, [
                'subdomain' => $variant,
            ]);

        $response->assertRedirect('/admin/companies/' . $targetTenant->id . '/edit');
        $response->assertSessionHasErrors('subdomain');
        $this->assertSame('existing', SubdomainNormalizer::normalize($variant));
    }
}

