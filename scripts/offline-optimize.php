<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

chdir(__DIR__ . '/..');

// Skip if dependencies or .env are missing (Codex/CI safe)
if (! file_exists(__DIR__ . '/../vendor/autoload.php')) {
    fwrite(STDOUT, "offline-optimize: vendor/autoload.php missing, skipping.\n");
    exit(0);
}

if (! file_exists(__DIR__ . '/../.env')) {
    fwrite(STDOUT, "offline-optimize: .env not found, skipping optimization.\n");
    exit(0);
}

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(Kernel::class);

$kernel->call('config:cache');
$kernel->call('route:cache');
$kernel->call('view:cache');

fwrite(STDOUT, "offline-optimize: config, routes and views cached.\n");
