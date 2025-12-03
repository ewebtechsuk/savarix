<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AgencyController as AdminAgencyController;
use App\Http\Controllers\Admin\AgencyUserController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AgencyRegisterController;
use App\Http\Controllers\Auth\MagicLoginController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiaryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\Landing\HomeController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
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

// Tenant routes (aktonz.savirix.com, etc.)
Route::group([
    'middleware' => [
        'auth:web,tenant',
        'tenancy',
        'preventAccessFromCentralDomains',
        'setTenantRouteDefaults',
        'role:' . \App\Support\AgencyRoles::propertyManagersPipe(),
    ],
], function () {
    Route::resource('properties', PropertyController::class);
    Route::get('contacts/search', [ContactController::class, 'search'])->name('contacts.search');
    Route::get('contacts/properties/search', [ContactController::class, 'searchProperties'])->name('contacts.properties.search');
    Route::post('contacts/bulk', [ContactController::class, 'bulk'])->name('contacts.bulk');
    Route::post('contacts/{contact}/notes', [ContactController::class, 'addNote'])->name('contacts.addNote');
    Route::delete('contacts/{contact}/notes/{note}', [ContactController::class, 'deleteNote'])->name('contacts.notes.destroy');
    Route::put('contacts/{contact}/notes/{note}', [ContactController::class, 'updateNote'])->name('contacts.notes.update');
    Route::patch('contacts/{contact}/notes/{note}/inline', [ContactController::class, 'apiUpdateNote'])->name('contacts.notes.inline.update');
    Route::delete('contacts/{contact}/notes/{note}/inline', [ContactController::class, 'apiDeleteNote'])->name('contacts.notes.inline.destroy');

    Route::post('contacts/{contact}/communications', [ContactController::class, 'addCommunication'])->name('contacts.addCommunication');
    Route::delete('contacts/{contact}/communications/{communication}', [ContactController::class, 'deleteCommunication'])->name('contacts.communications.destroy');
    Route::put('contacts/{contact}/communications/{communication}', [ContactController::class, 'updateCommunication'])->name('contacts.communications.update');
    Route::patch('contacts/{contact}/communications/{communication}/inline', [ContactController::class, 'apiUpdateCommunication'])->name('contacts.communications.inline.update');
    Route::delete('contacts/{contact}/communications/{communication}/inline', [ContactController::class, 'apiDeleteCommunication'])->name('contacts.communications.inline.destroy');
    Route::get('contacts/{contact}/communications/{communication}/edit', [ContactController::class, 'editCommunication'])->name('contacts.editCommunication');

    Route::post('contacts/{contact}/viewings', [ContactController::class, 'addViewing'])->name('contacts.addViewing');
    Route::delete('contacts/{contact}/viewings/{viewing}', [ContactController::class, 'deleteViewing'])->name('contacts.viewings.destroy');
    Route::put('contacts/{contact}/viewings/{viewing}', [ContactController::class, 'updateViewing'])->name('contacts.viewings.update');
    Route::patch('contacts/{contact}/viewings/{viewing}/inline', [ContactController::class, 'apiUpdateViewing'])->name('contacts.viewings.inline.update');
    Route::delete('contacts/{contact}/viewings/{viewing}/inline', [ContactController::class, 'apiDeleteViewing'])->name('contacts.viewings.inline.destroy');
    Route::get('contacts/{contact}/viewings/{viewing}/edit', [ContactController::class, 'editViewing'])->name('contacts.editViewing');

    Route::post('contacts/{contact}/assign-property', [ContactController::class, 'assignProperty'])->name('contacts.assignProperty');

    Route::resource('contacts', ContactController::class);

    Route::post('properties/{property}/assign-landlord', [PropertyController::class, 'assignLandlord'])->name('properties.assignLandlord');
    Route::resource('diary', DiaryController::class);
    Route::resource('accounts', AccountController::class);
    Route::resource('inspections', InspectionController::class);
    Route::resource('workflows', \App\Http\Controllers\WorkflowController::class);
    Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');
    Route::post('/documents/{document}/sign', [DocumentController::class, 'sign'])->name('documents.sign');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/documents/{document}/download/signed', [DocumentController::class, 'downloadSigned'])->name('documents.downloadSigned');
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

Route::group(['middleware' => ['tenancy', 'preventAccessFromCentralDomains', 'setTenantRouteDefaults', 'role:Tenant']], function () {
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
    'middleware' => ['tenancy', 'preventAccessFromCentralDomains', 'setTenantRouteDefaults', 'role:Agent'],
    'prefix' => 'agent',
    'as' => 'agent.',
], function () {
    Route::resource('inspections', InspectionController::class);
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
