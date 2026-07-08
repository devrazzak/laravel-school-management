<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\StudentController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\MyProfileController;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('set-password/verify', [AuthController::class, 'verifySetPasswordLink'])->name('api.set-password.verify')->middleware('throttle:5,1', ValidateSignature::class);
    Route::post('set-password/resend', [AuthController::class, 'resendSetPasswordLink'])->middleware('throttle:5,1');
    Route::post('set-password', [AuthController::class, 'setPassword'])->name('api.set-password')->middleware('throttle:5,1');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    // Protected routes
    Route::middleware(['auth:sanctum', 'ability:access-api'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::apiResource('students', StudentController::class);
        Route::apiResource('users', UserController::class);
        Route::get('me', [MyProfileController::class, 'show']);
        Route::put('me', [MyProfileController::class, 'update']);
    });
});
