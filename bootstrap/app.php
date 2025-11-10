<?php

use Illuminate\Foundation\Application;
use App\Support\AppKeyManager;

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

// Ensure APP_KEY is present/valid/persisted (works with APP_CIPHER)
AppKeyManager::ensure();

return $app;
