<?php

use App\Core\Application;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantPortalController;
use App\Tenancy\TenantDirectory;
use App\Http\Middleware\Authenticate;
use Framework\Http\Response;
use Illuminate\Support\Facades\Auth;

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
}, ['auth:web']);

$tenantDirectory = new TenantDirectory();

$router->get('/tenant/login', function ($request, array $context) use ($tenantDirectory) {
    $controller = new TenantPortalController($tenantDirectory);
    return $controller->login($request, $context);
});

$router->get('/tenant/dashboard', function ($request, array $context) use ($tenantDirectory) {
    $controller = new TenantPortalController($tenantDirectory);
    return $controller->dashboard($request, $context);
}, ['auth:tenant']);

$router->get('/tenant/list', function ($request, array $context) use ($tenantDirectory) {
    $controller = new TenantPortalController($tenantDirectory);
    return $controller->list($request, $context);
});

$router->get('/', function ($request, array $context) {
    $app = $context['app'] ?? null;

    if (!$app instanceof Application) {
        return new Response('Application not available', 500);
    }

    if (Auth::check()) {
        return Response::redirect('/dashboard', 302);
    }

    $content = $app->view('marketing.app');

    return Response::view($content);
});


return $app;
