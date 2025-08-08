<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandlordDashboardController;

Route::middleware(['web', 'auth:landlord'])
    ->prefix('landlord')
    ->name('landlord.')
    ->group(function () {
        Route::get('/dashboard', [LandlordDashboardController::class, 'index'])
            ->name('dashboard');
    });
