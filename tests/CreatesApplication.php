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

        $configuredKey = (string) env('APP_KEY', '');

        if (! $this->isValidAppKey($configuredKey)) {
            $configuredKey = 'base64:' . base64_encode(random_bytes(32));
        }

        $databasePath = $app->databasePath('database.sqlite');

        if (file_exists($databasePath)) {
            unlink($databasePath);
        }

        touch($databasePath);

        foreach ([
            'APP_KEY' => $configuredKey,
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $databasePath,
            'TENANCY_CENTRAL_CONNECTION' => 'sqlite',
            'DB_HOST' => '127.0.0.1',
        ] as $name => $value) {
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv($name.'='.$value);
        }

        $app->make(Kernel::class)->bootstrap();

        $app['config']->set('app.key', $configuredKey);
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', $databasePath);

        $sqliteConnection = $app['config']->get('database.connections.sqlite');

        $app['config']->set('database.connections.mysql', $sqliteConnection);
        $app['config']->set('database.connections.central', $sqliteConnection);
        $app['config']->set('tenancy.database.central_connection', 'sqlite');

        $migrations = [
            'database/migrations/2014_10_12_000000_create_users_table.php',
            'database/migrations/2019_09_15_000010_create_tenants_table.php',
            'database/migrations/2019_09_15_000020_create_domains_table.php',
            'database/migrations/2025_07_19_000000_add_email_verified_at_to_users_table.php',
            'database/migrations/2025_07_29_000001_add_is_admin_to_users_table.php',
            'database/migrations/2025_08_01_000001_add_login_token_to_users_table.php',
            'database/migrations/2025_07_29_150000_create_inspections_table.php',
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

    private function isValidAppKey(string $key): bool
    {
        if ($key === '') {
            return false;
        }

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            if ($decoded === false) {
                return false;
            }

            return strlen($decoded) === 16 || strlen($decoded) === 32;
        }

        $length = strlen($key);

        return $length === 16 || $length === 32;
    }
}
