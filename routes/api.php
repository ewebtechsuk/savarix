<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyApiController;
use App\Http\Controllers\Api\TenancyApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\PartnerIntegrationController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\WebhookApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Api\ContactApiController;
use App\Http\Controllers\Api\ApplicantController;
use App\Http\Controllers\Api\MarketingLeadController;
use App\Http\Controllers\Api\MarketingAnalyticsController;

Route::post('login', [AuthApiController::class, 'login']);

Route::post('marketing/leads', [MarketingLeadController::class, 'store'])
    ->name('api.marketing.leads.store');
Route::post('marketing/events', [MarketingAnalyticsController::class, 'store'])
    ->name('api.marketing.events.store');

Route::group([
    'middleware' => 'auth:sanctum',
    'as' => 'api.',
], function () {
    Route::resource('properties', PropertyApiController::class, ['except' => ['create', 'edit']]);
    Route::resource('tenancies', TenancyApiController::class, ['except' => ['create', 'edit']]);
    Route::resource('payments', PaymentApiController::class, ['except' => ['create', 'edit']]);
    Route::resource('contacts', ContactApiController::class, ['except' => ['create', 'edit']]);
    Route::resource('applicants', ApplicantController::class, ['except' => ['create', 'edit']]);
    Route::resource('webhooks', WebhookApiController::class, ['only' => ['index', 'store', 'destroy']]);
    Route::apiResource('integrations', PartnerIntegrationController::class);
    Route::get('dashboard/unified', [DashboardApiController::class, 'unified'])
        ->name('api.dashboard.unified');
});

/*(Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');*/


// Route::resource('property','PropertiesController', ['only'=>['index', 'show', 'store', 'update', 'destroy']]);
// Route::resource('landlord','LandlordsController', ['only'=>['index', 'show', 'store', 'update', 'destroy']]);

// Route::group(['middleware'=>'token'],function(){
// 	Route::post('import-property','PropertiesController@importProperties');
// });

Route::post('/signing/callback', [DocumentController::class, 'callback'])->name('signing.callback');
