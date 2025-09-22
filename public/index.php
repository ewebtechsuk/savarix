<?php

// Buffer bootstrap output so notices from legacy dependencies don't break HTTP headers.
ob_start();

require __DIR__ . '/../bootstrap/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

ob_end_clean();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';

$response = $app->handle($method, $path);

http_response_code($response->status());

foreach ($response->headers() as $name => $value) {
    $normalized = implode('-', array_map('ucfirst', explode('-', $name)));
    header($normalized . ': ' . $value, true);
}

echo $response->body();
