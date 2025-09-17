<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        if (is_callable([parent::class, 'boot'])) {
            parent::boot();
        }

        if (method_exists($this, 'routes')) {
            $this->routes(function () {
                Route::middleware('web')->group(base_path('routes/web.php'));

                foreach (['landlord', 'tenant', 'agent'] as $context) {
                    $path = base_path("routes/{$context}.php");

                    if (file_exists($path)) {
                        Route::middleware('web')->group($path);
                    }
                }

                Route::middleware('api')
                    ->prefix('api')
                    ->group(base_path('routes/api.php'));
            });
        }
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapTenantRoutes();

        $this->mapLandlordRoutes();

        $this->mapAgentRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function () {
            require base_path('routes/web.php');
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function () {
            require base_path('routes/api.php');
        });
    }

    /**
     * Define the "tenant" routes for the application.
     *
     * @return void
     */
    protected function mapTenantRoutes()
    {
        $path = base_path('routes/tenant.php');

        if (file_exists($path)) {
            Route::group([
                'middleware' => 'web',
                'namespace' => $this->namespace,
            ], function () use ($path) {
                require $path;
            });
        }
    }

    /**
     * Define the "landlord" routes for the application.
     *
     * @return void
     */
    protected function mapLandlordRoutes()
    {
        $path = base_path('routes/landlord.php');

        if (file_exists($path)) {
            Route::group([
                'middleware' => 'web',
                'namespace' => $this->namespace,
            ], function () use ($path) {
                require $path;
            });
        }
    }

    /**
     * Define the "agent" routes for the application.
     *
     * @return void
     */
    protected function mapAgentRoutes()
    {
        $path = base_path('routes/agent.php');

        if (file_exists($path)) {
            Route::group([
                'middleware' => 'web',
                'namespace' => $this->namespace,
            ], function () use ($path) {
                require $path;
            });
        }
    }
}
