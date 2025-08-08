<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'role:Tenant'
])->group(function () {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });

    Route::get('/tenancies/{tenancy}/payments/create', [PaymentController::class, 'create'])
        ->name('payments.create');
    Route::post('/tenancies/{tenancy}/payments', [PaymentController::class, 'store'])
        ->name('payments.store');
});

Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('stripe.webhook');

use App\Http\Controllers\TenantDashboardController;

Route::middleware(['web', 'auth:tenant'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])
            ->name('dashboard');
    });
