<?php

use App\Http\Controllers\Admin\AgencyController as AdminAgencyController;
use App\Http\Controllers\Admin\AgencyUserController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AgencyRegisterController;
use App\Http\Controllers\Auth\MagicLoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Landing\HomeController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantPortalController;
use App\Services\TenancyHealthReporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\LandlordDashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VerificationController;
use Stancl\Tenancy\Database\Models\Domain;

Route::get('/', HomeController::class)->name('marketing.home');

Route::get('/__health/tenancy', function (Request $request, TenancyHealthReporter $reporter) {
    $summary = $reporter->summary();
    $response = [
        'app_url' => $summary['app_url'],
        'central_domains' => $summary['central_domains'],
        'tenants_count' => $summary['tenants_count'],
        'domains_count' => $summary['domains_count'],
    ];

    if ($request->filled('host')) {
        $response['host_check'] = $reporter->inspectHost((string) $request->query('host'));
    }

    return response()->json($response);
})->middleware(['restrictToCentralDomains', 'auth', 'role:Admin|Landlord'])->name('tenancy.health');

Route::get('/__tenancy-debug', function (Request $request) {
    $centralDomains = config('tenancy.central_domains', []);
    $route = $request->route();
    $tenancyInitialized = function_exists('tenancy') && tenancy()->initialized;
    $tenantId = $tenancyInitialized ? optional(tenancy()->tenant)->getTenantKey() : null;
    $routeName = method_exists($route, 'getName') ? $route?->getName() : null;
    $domainRecord = Domain::query()
        ->where('domain', $request->getHost())
        ->first(['id', 'tenant_id', 'domain']);
    $defaults = method_exists(url(), 'getDefaultParameters') ? url()->getDefaultParameters() : [];

    return response()->json([
        'host' => $request->getHost(),
        'path' => $request->getPathInfo(),
        'full_url' => $request->fullUrl(),
        'is_central' => is_array($centralDomains) && in_array($request->getHost(), $centralDomains, true),
        'central_domains' => $centralDomains,
        'tenancy_initialized' => $tenancyInitialized,
        'tenant_id' => $tenantId,
        'route_name' => $routeName,
        'domain_record' => $domainRecord,
        'url_defaults' => $defaults['tenant'] ?? null,
    ]);
})->middleware(['auth', 'tenancyDebugAccess'])->name('tenancy.debug');

Route::group(['middleware' => 'guest'], function () {
    Route::get('/onboarding/register', [OnboardingController::class, 'showRegistrationForm'])
        ->name('onboarding.register');
    Route::post('/onboarding/register', [OnboardingController::class, 'register'])
        ->name('onboarding.register.store');

    Route::get('/signup/estate-agent', [AgencyRegisterController::class, 'create'])
        ->name('agency.register');

    Route::post('/signup/estate-agent', [AgencyRegisterController::class, 'store']);
});

// Simple test to confirm Laravel is handling this request
Route::get('/test-admin-path', function () {
    return 'admin-routes-ok';
});

// Secret Savarix owner admin routes (hidden URL prefix)
$secretAdminPath = env('SAVARIX_ADMIN_PATH', 'savarix-admin'); // DO NOT expose this publicly

Route::prefix($secretAdminPath)->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    });

    Route::middleware(['auth:web', 'owner'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/agencies', [AdminAgencyController::class, 'index'])->name('admin.agencies.index');
        Route::post('/agencies', [AdminAgencyController::class, 'store'])->name('admin.agencies.store');
        Route::get('/agencies/{agency}', [AdminAgencyController::class, 'show'])->name('admin.agencies.show');
        Route::put('/agencies/{agency}', [AdminAgencyController::class, 'update'])->name('admin.agencies.update');
        Route::delete('/agencies/{agency}', [AdminAgencyController::class, 'destroy'])->name('admin.agencies.destroy');

        Route::get('/agencies/{agency}/open', [AdminAgencyController::class, 'openTenant'])
            ->name('admin.agencies.open');

        Route::post('/agencies/{agency}/impersonate', [AdminAgencyController::class, 'impersonate'])
            ->name('admin.agencies.impersonate');

        Route::get('/agencies/{agency}/users', [AgencyUserController::class, 'index'])->name('admin.agencies.users.index');
        Route::post('/agencies/{agency}/users', [AgencyUserController::class, 'store'])->name('admin.agencies.users.store');
        Route::delete('/agencies/{agency}/users/{user}', [AgencyUserController::class, 'destroy'])->name('admin.agencies.users.destroy');
    });
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

Route::get('/magic-login/{token}', [MagicLoginController::class, 'login'])->name('magic.login');

Route::group(['prefix' => 'tenant'], function () {
    Route::get('login', [TenantPortalController::class, 'login'])->name('tenant.login');
    Route::get('list', [TenantPortalController::class, 'list'])->name('tenant.list');

    Route::group(['middleware' => 'auth:tenant'], function () {
        Route::get('dashboard', [TenantPortalController::class, 'dashboard'])
            ->name('tenant.dashboard');
    });
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

require __DIR__.'/auth.php';

// Temporary route to verify Hostinger mail configuration.
Route::get('/mail-test', function () {
    Mail::raw('Test email from Savarix', function ($message) {
        $message->to('savarix.dev@gmail.com')
                ->subject('Savarix Test Email');
    });

    return 'Mail sent';
});
