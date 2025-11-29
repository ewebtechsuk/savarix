<?php

namespace Tests;

use App\Http\Middleware\SetTenantRouteDefaults;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;


abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', database_path('database.sqlite'));

        if (! file_exists(database_path('database.sqlite'))) {
            touch(database_path('database.sqlite'));
        }

        $migrationResult = Artisan::call('migrate:fresh', ['--database' => 'sqlite', '--force' => true]);

        if ($migrationResult !== 0) {
            throw new \RuntimeException('Failed to migrate testing database: ' . Artisan::output());
        }

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    protected function useTenantDomain(string $tenantDomain = 'aktonz.savarix.com', string $centralDomain = 'savarix.com'): void
    {
        config()->set('app.url', 'https://' . $centralDomain);

        $centralDomains = config('tenancy.central_domains', []);
        $centralDomains = is_array($centralDomains) ? $centralDomains : [$centralDomains];

        config()->set('tenancy.central_domains', array_values(array_unique(array_filter(array_merge(
            [$centralDomain],
            $centralDomains
        )))));

        $request = $this->app['request'];
        $request->server->set('HTTP_HOST', $tenantDomain);
        $request->headers->set('host', $tenantDomain);

        app(SetTenantRouteDefaults::class)->handle($request, static function () {
            return response();
        });
    }
}
