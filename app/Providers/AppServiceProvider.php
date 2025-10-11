<?php

namespace App\Providers;

use App\Models\Property;
use App\Services\WorkflowEngine;
use App\Support\AppKeyManager;
use App\Support\ModelChangeRecorder;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
