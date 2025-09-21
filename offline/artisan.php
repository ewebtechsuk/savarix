<?php

$basePath = dirname(__DIR__);

require __DIR__ . '/bootstrap.php';

use Offline\Routing\RouteCollector;
use Offline\Routing\RouteFacade;
use Offline\Support\Env;
use Offline\Support\RouteLoader;
use Offline\Support\Table;

$command = $argv[1] ?? 'list';
$arguments = array_slice($argv, 2);

switch ($command) {
    case 'list':
        echo "[offline] Available commands: route:list, key:generate, config:clear, cache:clear, view:clear, route:clear, config:cache, route:cache, view:cache." . PHP_EOL;
        exit(0);
    case 'route:list':
        $collector = new RouteCollector();
        RouteFacade::setCollector($collector);
        RouteLoader::load($basePath);
        $routes = $collector->getRoutes();

        $rows = [];
        foreach ($routes as $route) {
            $rows[] = [
                implode('|', $route['methods']),
                $route['uri'],
                $route['name'] ?? '',
                implode(', ', $route['middleware']),
                $route['action'],
            ];
        }

        Table::render(['Method', 'URI', 'Name', 'Middleware', 'Action'], $rows);
        exit(0);

    case 'key:generate':
        if (Env::setAppKey($basePath)) {
            echo "[offline] Application key set successfully.\n";
            exit(0);
        }

        fwrite(STDERR, "[offline] Unable to write APP_KEY to .env.\n");
        exit(1);

    case 'config:clear':
    case 'cache:clear':
    case 'view:clear':
    case 'route:clear':
    case 'config:cache':
    case 'route:cache':
    case 'view:cache':
        echo "[offline] {$command} skipped (Composer dependencies unavailable).\n";
        exit(0);

    case 'test':
        $binaryCandidates = [
            $basePath . '/vendor/bin/phpunit',
            $basePath . '/deps/vendor/bin/phpunit',
        ];

        $phpunit = null;
        foreach ($binaryCandidates as $candidate) {
            if (is_file($candidate)) {
                $phpunit = $candidate;
                break;
            }
        }

        if ($phpunit === null) {
            fwrite(STDERR, "[offline] phpunit binary not found. Ensure composer dependencies are installed.\n");
            exit(1);
        }

        $phpBinary = PHP_BINARY ?: 'php';
        $processArgs = array_merge([$phpBinary, $phpunit], $arguments);
        $escaped = array_map(static function ($arg) {
            return escapeshellarg($arg);
        }, $processArgs);
        $commandLine = implode(' ', $escaped);

        passthru($commandLine, $status);
        exit($status);

    default:
        fwrite(STDERR, "[offline] Command '{$command}' is not available without Composer dependencies.\n");
        exit(1);
}
