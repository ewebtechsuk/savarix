<?php

namespace Tests;

use App\Models\User;
use App\Tenancy\TenantRepositoryManager;
use Database\Seeders\TenantFixtures;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;


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

        User::truncate();

        Auth::shouldUse('web');
        Auth::guard('web')->logout();
        Auth::guard('tenant')->logout();

        TenantRepositoryManager::clear();
        TenantFixtures::seed();


        return $app;
    }
}
