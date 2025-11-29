<?php

namespace App\Providers;

use App\Models\Property;
use App\Models\SavarixTenancy;
use App\Services\WorkflowEngine;
use App\Support\AppKeyManager;
use App\Support\ModelChangeRecorder;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        Relation::morphMap([
            'tenancy' => SavarixTenancy::class,
            'App\\Models\\Tenancy' => SavarixTenancy::class,
        ]);

        $recorder = app(ModelChangeRecorder::class);

        Property::saving(function (Property $property) use ($recorder) {
            $recorder->record($property);
        });

        Property::saved(function (Property $property) use ($recorder) {
            $original = $recorder->pull($property);
            $changes = [];

            $current = $property->getAttributes();

            foreach (array_unique(array_merge(array_keys($original), array_keys($current))) as $attribute) {
                $from = $original[$attribute] ?? null;
                $to = $current[$attribute] ?? null;

                if ($from !== $to) {
                    $changes[$attribute] = [
                        'from' => $from,
                        'to' => $to,
                    ];
                }
            }

            $context = [
                'model_id' => $property->getKey(),
                'changes' => $changes,
            ];

            app(WorkflowEngine::class)->processModelEvent('saved', $property, $context);

            if (isset($changes['status'])) {
                app(WorkflowEngine::class)->processModelEvent('property.status_changed', $property, $context);
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        AppKeyManager::ensure();

        $this->app->singleton(ModelChangeRecorder::class);

        $isLocalLikeEnvironment = $this->app->environment('local', 'development', 'testing');

        if ($this->app->environment('testing')) {
            $databasePath = database_path('testing.sqlite');

            if (! file_exists($databasePath)) {
                touch($databasePath);
            }

            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => $databasePath,
            ]);
        }

        if ($isLocalLikeEnvironment && config('database.default') === 'sqlite') {
            $this->normalizeSqliteConnectionPaths();
            $this->ensureSqliteDatabaseFilesExist();
        }
    }

    protected function normalizeSqliteConnectionPaths(): void
    {
        $connections = config('database.connections', []);

        foreach ($connections as $name => $connection) {
            if (($connection['driver'] ?? null) !== 'sqlite') {
                continue;
            }

            $database = $connection['database'] ?? null;

            if (! is_string($database) || $database === '' || $database === ':memory:') {
                continue;
            }

            $resolvedPath = Str::startsWith($database, [DIRECTORY_SEPARATOR, '\\'])
                || preg_match('/^[A-Za-z]:\\\\/', $database) === 1
                ? $database
                : base_path($database);

            if ($resolvedPath !== $database) {
                config(["database.connections.{$name}.database" => $resolvedPath]);
            }
        }
    }

    protected function ensureSqliteDatabaseFilesExist(): void
    {
        $connections = config('database.connections', []);

        foreach ($connections as $connection) {
            if (($connection['driver'] ?? null) !== 'sqlite') {
                continue;
            }

            $database = $connection['database'] ?? null;

            if (! is_string($database) || $database === '' || $database === ':memory:') {
                continue;
            }

            $directory = dirname($database);

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if (! file_exists($database)) {
                touch($database);
            }
        }
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                optional($request->user())->getAuthIdentifier() ?: $request->ip()
            );
        });
    }

}
