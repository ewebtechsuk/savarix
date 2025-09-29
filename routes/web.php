<?php

use App\Http\Controllers\Auth\MagicLoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DiaryController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MaintenanceRequestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\LandlordDashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VerificationController;

Route::get('/', function () {
    return view('landing.home');
})->name('marketing.home');

Route::group(['middleware' => 'guest'], function () {
    Route::get('/onboarding/register', [OnboardingController::class, 'showRegistrationForm'])
        ->name('onboarding.register');
    Route::post('/onboarding/register', [OnboardingController::class, 'register'])
        ->name('onboarding.register.store');
});

// Single dashboard route for route('dashboard')
Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});


// Central app routes (localhost:8888/)
Route::group(['middleware' => 'auth'], function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Central dashboard routes (should NOT use tenancy middleware)
Route::group(['middleware' => ['auth', 'verified', 'role:Admin|Landlord']], function () {
    // Remove duplicate dashboard route
    Route::post('/dashboard', [DashboardController::class, 'store'])->name('dashboard.store');
    Route::delete('/dashboard/{id}', [DashboardController::class, 'destroy'])->name('dashboard.destroy');
    Route::get('/dashboard/impersonate/{id}', [DashboardController::class, 'impersonate'])->name('dashboard.impersonate');

    // Tenant management routes (landlord app only)
    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
    Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
    Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
    Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
    Route::get('/tenants/{tenant}/delete', [TenantController::class, 'delete'])->name('tenants.delete');
    Route::post('/tenants/{tenant}/add-user', [TenantController::class, 'addUser'])->name('tenants.addUser');

    // Maintenance request admin routes
    Route::get('/maintenance', [MaintenanceRequestController::class, 'index'])->name('maintenance.index');
    Route::get('/maintenance/{maintenanceRequest}', [MaintenanceRequestController::class, 'show'])->name('maintenance.show');
    Route::put('/maintenance/{maintenanceRequest}', [MaintenanceRequestController::class, 'update'])->name('maintenance.update');
});

// Tenant routes (aktonz.ressapp.com, etc.)
Route::group(['middleware' => ['auth', 'tenancy', 'role:Tenant']], function () {
    Route::resource('properties', PropertyController::class);
    Route::resource('contacts', ContactController::class);
    Route::resource('diary', DiaryController::class);
    Route::resource('accounts', AccountController::class);
    Route::resource('inspections', InspectionController::class);
    Route::resource('workflows', \App\Http\Controllers\WorkflowController::class);
    Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');
    Route::post('/documents/{document}/sign', [DocumentController::class, 'sign'])->name('documents.sign');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('maintenance/create', [MaintenanceRequestController::class, 'create'])->name('maintenance.create');
    Route::post('maintenance', [MaintenanceRequestController::class, 'store'])->name('maintenance.store');
});

Route::get('/magic-login/{token}', [MagicLoginController::class, 'login'])->name('magic.login');

Route::group(['prefix' => 'tenant'], function () {
    Route::get('login', [TenantPortalController::class, 'login'])->name('tenant.login');
    Route::get('list', [TenantPortalController::class, 'list'])->name('tenant.list');

    Route::group(['middleware' => 'auth:tenant'], function () {
        Route::get('dashboard', [TenantPortalController::class, 'dashboard'])
            ->name('tenant.dashboard');
    });
});

Route::group(['middleware' => ['tenancy', 'preventAccessFromCentralDomains', 'role:Tenant']], function () {
    Route::group(['prefix' => 'onboarding'], function () {
        Route::get('verification/start', [VerificationController::class, 'start'])->name('verification.start');
        Route::get('verification/status', [VerificationController::class, 'status'])->name('verification.status');
    });

    Route::get('/tenancies/{tenancy}/payments/create', [PaymentController::class, 'create'])
        ->name('payments.create');
    Route::post('/tenancies/{tenancy}/payments', [PaymentController::class, 'store'])
        ->name('payments.store');
});

Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('stripe.webhook');

Route::post('/webhooks/onfido', [VerificationController::class, 'callback'])->name('verification.callback');

Route::group([
    'middleware' => 'auth:landlord',
    'prefix' => 'landlord',
    'as' => 'landlord.',
], function () {
    Route::get('/dashboard', [LandlordDashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('tenants', TenantController::class);
});

Route::group([
    'middleware' => ['tenancy', 'preventAccessFromCentralDomains', 'role:Agent'],
    'prefix' => 'agent',
    'as' => 'agent.',
], function () {
    Route::resource('inspections', InspectionController::class);
});

require __DIR__.'/auth.php';
