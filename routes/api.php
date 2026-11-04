<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DetectionVerificationController;
use App\Http\Controllers\Api\MissingPersonController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PushTokenController;
use App\Http\Controllers\Api\SightingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Python Backend Integration
|--------------------------------------------------------------------------
|
| Called by the Python face-recognition backend (X-API-Key header, applied
| globally via the 'api.key' middleware in bootstrap/app.php).
|
*/

Route::middleware('api.key')->post('/verify-detection', [DetectionVerificationController::class, 'store']);

/*
|--------------------------------------------------------------------------
| API Routes — Public Mobile App
|--------------------------------------------------------------------------
|
| Available to guests and the mobile app without authentication.
|
*/

Route::middleware('throttle:10,1')->post('/login', [AuthController::class, 'login']);

Route::get('/missing-persons', [MissingPersonController::class, 'index'])->middleware('throttle:120,1');
Route::post('/track-case', [\App\Http\Controllers\ReportingController::class, 'track'])->middleware('throttle:10,1');
Route::post('/missing-persons', [MissingPersonController::class, 'store'])->middleware(['throttle:10,1', \App\Http\Middleware\SubmissionReceipt::class]);
Route::get('/missing-persons/{missingPerson}', [MissingPersonController::class, 'show'])->middleware('throttle:120,1');
Route::post('/missing-persons/{missingPerson}/sightings', [SightingController::class, 'store'])->middleware(['throttle:20,1', \App\Http\Middleware\SubmissionReceipt::class]);

/*
|--------------------------------------------------------------------------
| API Routes — Authenticated Mobile App (Bearer token)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'show']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    Route::post('/push-tokens', [PushTokenController::class, 'store']);
    Route::delete('/push-tokens', [PushTokenController::class, 'destroy']);
    Route::post('/push-tokens/test', [PushTokenController::class, 'test']);

    // Staff-only actions (super admin, PDRM officer, patrol unit)
    Route::middleware('api.role:super_admin,pdrm_officer,patrol_unit')->group(function () {
        Route::get('/missing-persons/{missingPerson}/map', [\App\Http\Controllers\ReportingController::class, 'map']);
        Route::get('/evidence/{evidence}', [\App\Http\Controllers\ReportingController::class, 'evidence']);
        Route::patch('/missing-persons/{missingPerson}/status', [MissingPersonController::class, 'updateStatus']);
        Route::get('/sightings', [SightingController::class, 'index']);
        Route::patch('/sightings/{sighting}/status', [SightingController::class, 'updateStatus']);
    });
});
