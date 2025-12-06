<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiaryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyMediaController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WorkflowController;
use App\Support\AgencyRoles;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

$tenantDomainMiddleware = [
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'setTenantRouteDefaults',
];

Route::middleware(array_merge($tenantDomainMiddleware, ['auth:web,tenant', 'verified']))->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::group([
    'middleware' => array_merge($tenantDomainMiddleware, [
        'auth:web,tenant',
        'role:' . AgencyRoles::propertyManagersPipe() . '|agency_admin',
    ]),
], function () {
    Route::resource('properties', PropertyController::class)
        ->where(['property' => '[0-9]+']);

    Route::delete('/properties/{property}/media/{media}', [PropertyMediaController::class, 'destroy'])
        ->name('properties.media.destroy')
        ->whereNumber('property')
        ->whereNumber('media');

    Route::post('properties/{property}/assign-landlord', [PropertyController::class, 'assignLandlord'])
        ->name('properties.assignLandlord')
        ->whereNumber('property');

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
    Route::get('contacts/{contact}/communications/{communication}/edit', [ContactController::class, 'editCommunication'])->name('contacts.communications.edit');

    Route::post('contacts/{contact}/viewings', [ContactController::class, 'addViewing'])->name('contacts.addViewing');
    Route::delete('contacts/{contact}/viewings/{viewing}', [ContactController::class, 'deleteViewing'])->name('contacts.viewings.destroy');
    Route::put('contacts/{contact}/viewings/{viewing}', [ContactController::class, 'updateViewing'])->name('contacts.viewings.update');
    Route::patch('contacts/{contact}/viewings/{viewing}/inline', [ContactController::class, 'apiUpdateViewing'])->name('contacts.viewings.inline.update');
    Route::delete('contacts/{contact}/viewings/{viewing}/inline', [ContactController::class, 'apiDeleteViewing'])->name('contacts.viewings.inline.destroy');
    Route::get('contacts/{contact}/viewings/{viewing}/edit', [ContactController::class, 'editViewing'])->name('contacts.viewings.edit');

    Route::post('contacts/{contact}/assign-property', [ContactController::class, 'assignProperty'])->name('contacts.assignProperty');

    Route::resource('contacts', ContactController::class);

    Route::resource('diary', DiaryController::class);
    Route::resource('accounts', AccountController::class);
    Route::resource('inspections', InspectionController::class);
    Route::resource('workflows', WorkflowController::class);

    Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');
    Route::post('/documents/{document}/sign', [DocumentController::class, 'sign'])->name('documents.sign');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/documents/{document}/download/signed', [DocumentController::class, 'downloadSigned'])->name('documents.downloadSigned');

    Route::get('maintenance/create', [MaintenanceRequestController::class, 'create'])->name('maintenance.create');
    Route::post('maintenance', [MaintenanceRequestController::class, 'store'])->name('maintenance.store');
});

Route::group([
    'middleware' => array_merge($tenantDomainMiddleware, ['role:Tenant']),
], function () {
    Route::group(['prefix' => 'onboarding'], function () {
        Route::get('verification/start', [VerificationController::class, 'start'])->name('verification.start');
        Route::get('verification/status', [VerificationController::class, 'status'])->name('verification.status');
    });

    Route::get('/tenancies/{tenancy}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/tenancies/{tenancy}/payments', [PaymentController::class, 'store'])->name('payments.store');
});

Route::group([
    'middleware' => array_merge($tenantDomainMiddleware, ['role:Agent']),
    'prefix' => 'agent',
    'as' => 'agent.',
], function () {
    Route::resource('inspections', InspectionController::class);
});
