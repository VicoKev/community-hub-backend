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

});