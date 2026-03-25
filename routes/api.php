<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

/*
 ROUTES PUBLIQUES
 */
// Annuaire & Recherche
Route::prefix('v1/profiles')->name('api.v1.profiles.')->group(function (): void {
    Route::get('/', [ProfileController::class, 'index'])
        ->middleware('throttle:60,1')
        ->name('index');

    Route::get('/{profile}', [ProfileController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('show');
});

// Routes d'authentification
Route::prefix('v1/auth')->name('api.v1.auth.')->group(function (): void {

    // Inscription
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register');

    // Connexion
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login');

    // Mot de passe oublié
    Route::post('password/forgot', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,10')
        ->name('password.forgot');

    // Réinitialisation du mot de passe
    Route::post('password/reset', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:5,10')
        ->name('password.reset');

    // Routes protégées (sous le même préfixe mais avec middleware)
    Route::middleware('auth:api')->group(function (): void {

        // Vérification email
        Route::prefix('email')->name('email.')->group(function (): void {

            // Vérifier le code de vérification reçu par email
            Route::post('verify', [AuthController::class, 'verifyCode'])
                ->middleware('throttle:5,1')
                ->name('verify');

            // Renvoyer un nouveau code de vérification
            Route::post('resend', [AuthController::class, 'resendVerifyCode'])
                ->middleware('throttle:3,1')
                ->name('resend');
        });

        // Déconnexion
        Route::post('logout', [AuthController::class, 'logout'])
            ->name('logout');

    });

});

/*
ROUTES AUTHENTIFIÉES
*/
Route::middleware(['auth:api', 'verified'])->group(function (): void {

    // Profil personnel
    Route::prefix('v1/profiles')->name('api.v1.profiles.auth.')->group(function (): void {
        // Voir son propre profil
        Route::get('/me', [ProfileController::class, 'myProfile'])
            ->name('me');

        // Créer son profil
        Route::post('/', [ProfileController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('store');

        // Modifier son profil
        Route::put('/{profile}', [ProfileController::class, 'update'])
            ->middleware('throttle:10,1')
            ->name('update');

        // Supprimer son profil
        Route::delete('/{profile}', [ProfileController::class, 'destroy'])
            ->name('destroy');

        // Upload de fichiers
        Route::post('/{profile}/files', [ProfileController::class, 'uploadFile'])
            ->middleware('throttle:10,1')
            ->name('files.upload');

        // Supprimer un fichier
        Route::delete('/{profile}/files/{mediaId}', [ProfileController::class, 'deleteFile'])
            ->name('files.delete');
    });

});

/*
ROUTES MODÉRATEUR
*/
Route::middleware(['auth:api', 'verified', 'role:moderator|admin|super_admin'])
    ->prefix('v1/admin')
    ->name('api.v1.admin.')
    ->group(function (): void {

        // Voir tous les profils
        Route::get('/profiles', [AdminController::class, 'profiles'])
            ->name('profiles.index');

        // Valider un profil
        Route::post('/profiles/{profile}/approve', [AdminController::class, 'approveProfile'])
            ->name('profiles.approve');

        // Rejeter un profil
        Route::post('/profiles/{profile}/reject', [AdminController::class, 'rejectProfile'])
            ->name('profiles.reject');

    });

/*
ROUTES ADMIN
*/
Route::middleware(['auth:api', 'verified', 'role:admin|super_admin'])
    ->prefix('v1/admin')
    ->name('api.v1.admin.')
    ->group(function (): void {

        // Dashboard & Stats
        Route::get('/stats', [AdminController::class, 'stats'])
            ->name('stats');

        // Gestion des utilisateurs
        Route::get('/users', [AdminController::class, 'users'])
            ->name('users.index');

        Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])
            ->middleware('permission:user.assign_role')
            ->name('users.role');

        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])
            ->middleware('permission:user.delete_any')
            ->name('users.delete');

        // Export
        Route::get('/export/profiles', [AdminController::class, 'exportProfiles'])
            ->middleware('permission:export.profiles')
            ->name('export.profiles');

        // Logs d'activité
        Route::get('/logs', [AdminController::class, 'activityLogs'])
            ->middleware('permission:logs.view')
            ->name('logs');

    });
