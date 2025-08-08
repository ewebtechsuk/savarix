<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantDashboardController;

Route::middleware(['web', 'auth:tenant'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])
            ->name('dashboard');
    });
