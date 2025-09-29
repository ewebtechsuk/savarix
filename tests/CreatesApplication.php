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

        User::truncate();

        Auth::shouldUse('web');
        Auth::guard('web')->logout();
        Auth::guard('tenant')->logout();

        TenantRepositoryManager::clear();
        TenantFixtures::seed();


        return $app;
    }
}
