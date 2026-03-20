<?php

use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/forgot', [PasswordResetController::class, 'forgot']);
Route::post('/password/reset', [PasswordResetController::class, 'reset']);

// Routes authentifiées
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profil
    Route::put('/profile/me', [ProfileController::class, 'update']);
    Route::post('/profile/upload', [ProfileController::class, 'upload']);
});

// Routes admin
Route::middleware(['auth:api', 'role:admin,super-admin'])->group(function () {
    Route::patch('/admin/profiles/{id}/status', [AdminProfileController::class, 'updateStatus']);
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);
});
