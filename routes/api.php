<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyApiController;
use App\Http\Controllers\Api\TenancyApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\WebhookApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\V1\HealthCheckController;
use App\Http\Controllers\DocumentController;

Route::post('login', [AuthApiController::class, 'login']);

Route::prefix('v1')->group(function () {
    Route::get('health', HealthCheckController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('properties', PropertyApiController::class);
    Route::apiResource('tenancies', TenancyApiController::class);
    Route::apiResource('payments', PaymentApiController::class);
    Route::apiResource('webhooks', WebhookApiController::class)->only(['index', 'store', 'destroy']);
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