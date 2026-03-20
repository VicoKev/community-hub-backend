<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/forgot', [PasswordResetController::class, 'forgot']);
Route::post('/password/reset', [PasswordResetController::class, 'reset']);

// Routes authentifiées
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
