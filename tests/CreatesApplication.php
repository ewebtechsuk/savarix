<?php

namespace Tests;

use App\Core\Application;

trait CreatesApplication
{
    public function createApplication(): Application
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }
}
