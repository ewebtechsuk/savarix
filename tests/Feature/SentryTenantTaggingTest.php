<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Providers\SentryTenantContextServiceProvider;
use Sentry\State\Scope;
use Stancl\Tenancy\Database\Models\Domain;
use Tests\TestCase;

class FakeSentry
{
    public ?Scope $capturedScope = null;

    public function configureScope(callable $callback): void
    {
        $scope = new Scope();
        $callback($scope);
        $this->capturedScope = $scope;
    }
}

class SentryTenantTaggingTest extends TestCase
{
    public function test_scope_includes_tenant_when_tenancy_initialized(): void
    {
        $tenant = Tenant::factory()->create(['id' => 'aktonz']);

        Domain::create([
            'domain' => 'aktonz.savarix.com',
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->useTenantDomain('aktonz.savarix.com');
        tenancy()->initialize($tenant);

        try {
            $fakeSentry = $this->bootSentryScope();

            $this->assertNotNull($fakeSentry->capturedScope);

            $tags = $this->scopeTags($fakeSentry->capturedScope);
            $contexts = $this->scopeContexts($fakeSentry->capturedScope);

            $this->assertSame('aktonz.savarix.com', $tags['host'] ?? null);
            $this->assertSame('aktonz', $tags['tenant'] ?? null);
            $this->assertSame('aktonz', $contexts['tenant']['id'] ?? null);
            $this->assertSame('aktonz.savarix.com', $contexts['tenant']['domain'] ?? null);
        } finally {
            tenancy()->end();
        }
    }

    public function test_central_domain_sets_null_tenant_tag(): void
    {
        config()->set('tenancy.central_domains', ['savarix.com']);

        $request = $this->app['request'];
        $request->server->set('HTTP_HOST', 'savarix.com');
        $request->headers->set('host', 'savarix.com');

        $fakeSentry = $this->bootSentryScope();
        $tags = $this->scopeTags($fakeSentry->capturedScope);

        $this->assertArrayHasKey('host', $tags);
        $this->assertSame('savarix.com', $tags['host']);
        $this->assertNull($tags['tenant'] ?? null);
    }

    public function test_authenticated_user_role_is_tagged(): void
    {
        $user = User::factory()->create(['role' => 'Agency Admin']);
        $this->actingAs($user);

        $request = $this->app['request'];
        $request->server->set('HTTP_HOST', 'savarix.com');
        $request->headers->set('host', 'savarix.com');

        $fakeSentry = $this->bootSentryScope();
        $tags = $this->scopeTags($fakeSentry->capturedScope);

        $this->assertSame('Agency Admin', $tags['role'] ?? null);
    }

    private function bootSentryScope(): FakeSentry
    {
        $fakeSentry = new FakeSentry();
        $this->app->instance('sentry', $fakeSentry);

        $provider = $this->app->getProvider(SentryTenantContextServiceProvider::class)
            ?? new SentryTenantContextServiceProvider($this->app);

        $provider->boot();

        return $fakeSentry;
    }

    /**
     * @return array<string, string|null>
     */
    private function scopeTags(Scope $scope): array
    {
        $property = new \ReflectionProperty(Scope::class, 'tags');
        $property->setAccessible(true);

        /** @var array<string, string|null> $tags */
        $tags = $property->getValue($scope);

        return $tags;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function scopeContexts(Scope $scope): array
    {
        $property = new \ReflectionProperty(Scope::class, 'contexts');
        $property->setAccessible(true);

        /** @var array<string, array<string, mixed>> $contexts */
        $contexts = $property->getValue($scope);

        return $contexts;
    }
}
