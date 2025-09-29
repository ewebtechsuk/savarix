<?php

namespace Tests;

use App\Tenancy\TenantRepositoryManager;
use Database\Seeders\TenantFixtures;
use Database\Seeders\TenantPortalUserSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;


trait CreatesApplication
{
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $configuredKey = (string) env('APP_KEY', '');

        if ($configuredKey === '') {
            $configuredKey = 'base64:VNuWYLe0rTIOyH2PdBl8vmxlwmyEqDzEDDNGuphepaI=';

            foreach (['APP_KEY' => $configuredKey] as $name => $value) {
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
                putenv($name.'='.$value);
            }
        }

        $app['config']->set('app.key', $configuredKey);

        $databasePath = $app->databasePath('database.sqlite');

        if (file_exists($databasePath)) {
            unlink($databasePath);
        }

        touch($databasePath);

        $app['config']->set('database.default', env('DB_CONNECTION', 'sqlite'));
        $app['config']->set('database.connections.sqlite.database', env('DB_DATABASE', $databasePath));
        $app['config']->set('database.connections.central', $app['config']->get('database.connections.sqlite'));
        $app['config']->set('tenancy.database.central_connection', $app['config']->get('database.default'));

        $migrations = [
            'database/migrations/2014_10_12_000000_create_users_table.php',
            'database/migrations/2019_09_15_000010_create_tenants_table.php',
            'database/migrations/2019_09_15_000020_create_domains_table.php',
            'database/migrations/2025_07_19_000000_add_email_verified_at_to_users_table.php',
            'database/migrations/2025_07_29_000001_add_is_admin_to_users_table.php',
            'database/migrations/2025_08_01_000001_add_login_token_to_users_table.php',
        ];

        foreach ($migrations as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => $app['config']->get('database.default'),
                '--force' => true,
                '--path' => $migrationPath,
            ]);
        }

        Artisan::call('db:seed', [
            '--class' => TenantPortalUserSeeder::class,
            '--force' => true,
        ]);

        Auth::shouldUse('web');
        Auth::guard('web')->logout();
        Auth::guard('tenant')->logout();

        TenantRepositoryManager::clear();
        TenantFixtures::seed();


        return $app;
    }
}
