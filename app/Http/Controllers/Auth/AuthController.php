<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'nom'            => $request->nom,
            'prenom'         => $request->prenom,
            'email'          => $request->email,
            'password'       => $request->motDePasse,
            'telephone'      => $request->telephone,
            'date_naissance' => $request->dateNaissance,
            'genre'          => $request->genre,
            'statut'         => 'en_attente',
            'actif'          => false,
        ]);

        event(new Registered($user));

        return response()->json([
            'message'     => 'Inscription réussie. Votre compte est en attente de validation.',
            'utilisateur' => [
                'id'             => $user->id,
                'nom'            => $user->nom,
                'prenom'         => $user->prenom,
                'email'          => $user->email,
                'statut'         => $user->statut,
                'dateInscription' => $user->created_at,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt(['email' => $request->email, 'password' => $request->motDePasse])) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        $user = Auth::user();

        if ($user->statut !== 'valide') {
            Auth::logout();

            $messages = [
                'en_attente' => 'Votre compte est en attente de validation par un administrateur.',
                'rejete'     => 'Votre compte a été rejeté.',
                'suspendu'   => 'Votre compte a été suspendu.',
            ];

            return response()->json([
                'message' => $messages[$user->statut] ?? 'Accès refusé.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => 3600,
            'utilisateur'  => [
                'id'     => $user->id,
                'nom'    => $user->nom,
                'prenom' => $user->prenom,
                'email'  => $user->email,
                'role'   => $user->role,
                'statut' => $user->statut,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }
}
