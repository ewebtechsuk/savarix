<?php

namespace Tests;

use App\Http\Controllers\Tenant\LoginController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\Token;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware as MiddlewareConfigurator;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as BaseCsrfMiddleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Spatie\Permission\Middlewares\RoleOrPermissionMiddleware;
use Tests\Support\Middleware\BypassTenancy;

trait CreatesApplication
{
    /**
     * Bootstrap the application instance for feature tests.
     *
     * @return \Illuminate\Foundation\Application|\App\Core\Application
     */
    public function createApplication()
    {
        $basePath = realpath(__DIR__ . '/..');

        if (! isset($_ENV['APP_KEY']) || empty($_ENV['APP_KEY'])) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            putenv('APP_KEY=' . $key);
            $_ENV['APP_KEY'] = $key;
            $_SERVER['APP_KEY'] = $key;
        }

        if (class_exists(LaravelApplication::class) && method_exists(LaravelApplication::class, 'configure')) {
            $builder = LaravelApplication::configure($basePath)
                ->withRouting(
                    web: $basePath . '/routes/web.php',
                    api: $basePath . '/routes/api.php',
                    commands: $basePath . '/routes/console.php',
                    channels: $basePath . '/routes/channels.php'
                )
                ->withMiddleware(function (MiddlewareConfigurator $middleware): void {
                    $middleware->web(
                        replace: [
                            EncryptCookies::class => \App\Http\Middleware\EncryptCookies::class,
                            BaseCsrfMiddleware::class => VerifyCsrfToken::class,
                        ]
                    );

                    $middleware->alias([
                        'token' => Token::class,
                        'is_admin' => IsAdmin::class,
                        'tenancy' => BypassTenancy::class,
                        'role' => RoleMiddleware::class,
                        'permission' => PermissionMiddleware::class,
                        'role_or_permission' => RoleOrPermissionMiddleware::class,
                    ]);

                    $middleware->redirectTo(
                        guests: function ($request) {
                            if ($request->routeIs('tenant.*') || str_starts_with($request->path(), 'tenant/')) {
                                return url('/tenant/login');
                            }

                            return route('login');
                        }
                    );
                })
                ->withExceptions(fn (Exceptions $exceptions) => null);

            $app = $builder->create();
            $app->make(Kernel::class)->bootstrap();

            $app->make('config')->set('auth.guards.tenant.provider', 'users');

            if (! Route::has('tenant.login')) {
                Route::middleware('web')->get('/tenant/login', [LoginController::class, 'showLoginForm'])
                    ->name('tenant.login');
            }

            return $app;
        }

        $app = require $basePath . '/bootstrap/app.php';

        if ($app instanceof LaravelApplication) {
            $app->make(Kernel::class)->bootstrap();
        }

        return $app;
    }
}
