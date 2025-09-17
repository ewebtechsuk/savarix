<?php

use App\Core\Application;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\Authenticate;
use Framework\Http\Response;

$app = new Application(__DIR__ . '/..');

$router = $app->router();
$router->middleware('auth', [new Authenticate(), '__invoke']);

$router->get('/login', function ($request, array $context) {
    $controller = new LoginController();
    return $controller->show($request, $context);
});

$router->get('/dashboard', function ($request, array $context) {
    $controller = new DashboardController();
    return $controller->index($request, $context);
}, ['auth']);

$router->get('/', function () {
    return Response::redirect('/dashboard', 302);
});

return $app;
