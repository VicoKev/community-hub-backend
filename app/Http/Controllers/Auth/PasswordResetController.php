<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink(['email' => $request->email]);

        return response()->json([
            'message' => 'Si cette adresse email est associée à un compte, vous recevrez un lien de réinitialisation.',
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            [
                'email'                 => $request->email,
                'password'              => $request->motDePasse,
                'password_confirmation' => $request->motDePasse_confirmation,
                'token'                 => $request->token,
            ],
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Le lien de réinitialisation est invalide ou a expiré.'], 422);
        }

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }
}
