<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use App\Services\WorkflowEngine;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::saved(function ($model) {
            app(WorkflowEngine::class)->processModelEvent('saved', $model);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
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
}
