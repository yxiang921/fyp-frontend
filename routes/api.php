<?php

use App\Http\Controllers\Api\DetectionVerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Python Backend Integration
|--------------------------------------------------------------------------
|
| These routes are called by the Python face-recognition backend.
| All routes are protected by the 'api.key' middleware (X-API-Key header).
|
*/

Route::post('/verify-detection', [DetectionVerificationController::class, 'store']);
