<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MonitorController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('monitors', MonitorController::class);

        Route::get('monitors/{monitor}/history', [MonitorController::class, 'history']);
        Route::get('monitors/{monitor}/stats', [MonitorController::class, 'stats']);
    });
});
