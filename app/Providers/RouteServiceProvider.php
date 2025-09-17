<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define the routes for the application.
     */
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->group(base_path('routes/auth.php'));

            if (file_exists(base_path('routes/landlord.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/landlord.php'));
            }

            if (file_exists(base_path('routes/tenant.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/tenant.php'));
            }

            if (file_exists(base_path('routes/agent.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/agent.php'));
            }

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        });
    }
}
