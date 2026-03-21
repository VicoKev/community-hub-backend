<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Models\EmailVerification;
use App\Models\User;
use App\Services\MailService;
use App\Traits\JsonApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    use JsonApiResponse;

    public function __construct(
        private MailService $mailService
    ) {}

    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = DB::transaction(function () use ($request): array {
                $validated = $request->validated();
 
                $user = User::create([
                    'first_name' => $validated['first_name'],
                    'last_name'  => $validated['last_name'],
                    'email'      => $validated['email'],
                    'password'   => Hash::make($validated['password']),
                ]);
 
                $code = EmailVerification::generateCode();
 
                $user->emailVerifications()->create([
                    'code'       => $code,
                    'expires_at' => now()->addMinutes(10),
                ]);
 
                return ['user' => $user, 'code' => $code];
            });
 
            /** @var User $user */
            $user = $result['user'];
 
            $tokenResult = $user->createToken('register_token');
            // Définir expiration du token à 1 mois
            $tokenResult->token->expires_at = now()->addMonth();
            $tokenResult->token->save();

            $accessToken = $tokenResult->accessToken;
 
            try {
                $this->mailService->send(
                    $result['user']->email,
                    'emails.verify-code',
                    'Vérification de votre adresse email',
                    [
                        'code' => $result['code'],
                        'user' => [
                            'first_name' => $result['user']->first_name
                        ]
                    ],
                    false,
                );
            } catch (Throwable $e) {
                Log::warning("Échec d'envoi de l'e-mail de vérification", [
                    'email' => $result['user']->email,
                    'exception' => $e->getMessage(),
                ]);
            }
 
            return $this->createdResponse(
                message: 'Inscription réussie. Veuillez vérifier votre adresse email.',
                data: [
                    'user' => [
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                    ],
                    'token' => $accessToken,
                ],
            );
        } catch (Throwable $e) {
            Log::error('Erreur inscription', [
                'email'     => $request->email,
                'exception' => $e->getMessage(),
            ]);
 
            return $this->serverError('Une erreur est survenue lors de l\'inscription.');
        }
    }
}
