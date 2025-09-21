<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandlordDashboardController;
use App\Http\Controllers\TenantController;

Route::middleware(['web', 'auth:landlord'])
    ->prefix('landlord')
    ->name('landlord.')
    ->group(function () {
        Route::get('/dashboard', [LandlordDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('tenants', TenantController::class);
    });
