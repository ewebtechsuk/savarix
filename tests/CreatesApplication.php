<?php

namespace Tests;

use App\Core\Application;
use App\Models\User;
use App\Tenancy\TenantRepositoryManager;
use Database\Seeders\TenantFixtures;
use Illuminate\Support\Facades\Auth;


trait CreatesApplication
{
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        User::truncate();

        Auth::shouldUse('web');
        Auth::guard('web')->logout();
        Auth::guard('tenant')->logout();

        TenantRepositoryManager::clear();
        TenantFixtures::seed();


        return $app;
    }
}
