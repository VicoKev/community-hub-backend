<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

/* 
 ROUTES PUBLIQUES
 */
Route::prefix('v1/auth')->name('api.v1.auth.')->group(function (): void {
 
    // Inscription 
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('register');

    // Connexion
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login');

});

/* 
 ROUTES PROTÉGÉES
 */
Route::prefix('v1/auth')->name('api.v1.auth.')->middleware('auth:api')->group(function (): void {
            
    // Vérification email
    Route::prefix('email')->name('email.')->group(function (): void {
 
        // Vérifier le code de vérification reçu par email
        Route::post('verify', [AuthController::class, 'verifyCode'])
            ->middleware('throttle:10,1')
            ->name('verify');
 
        // Renvoyer un nouveau code de vérification
        Route::post('resend', [AuthController::class, 'resendVerifyCode'])
            ->middleware('throttle:3,1')
            ->name('resend');
 
    });
 
});