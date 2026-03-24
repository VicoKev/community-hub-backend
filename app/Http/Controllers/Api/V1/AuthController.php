<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Http\Requests\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\V1\Auth\VerifyCodeRequest;
use App\Models\EmailVerification;
use App\Models\User;
use App\Services\MailService;
use App\Traits\JsonApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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
            $accessToken = $tokenResult->accessToken;

            // try {
            //     $this->mailService->send(
            //         $result['user']->email,
            //         'emails.verify-code',
            //         'Vérification de votre adresse email',
            //         [
            //             'code' => $result['code'],
            //             'user' => [
            //                 'first_name' => $result['user']->first_name
            //             ]
            //         ],
            //         false,
            //     );
            // } catch (Throwable $e) {
            //     Log::warning("Échec d'envoi de l'e-mail de vérification", [
            //         'email' => $result['user']->email,
            //         'exception' => $e->getMessage(),
            //     ]);
            // }

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

    /**
     * Vérifier le code reçu par email
     */
    public function verifyCode(VerifyCodeRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(message: 'Votre adresse email est déjà vérifiée.');
        }

        $verification = $user->emailVerifications()
            ->where('code', $request->code)
            ->whereNull('verified_at')
            ->first();

        if (! $verification) {
            return $this->errorResponse('Code de vérification invalide.', 422);
        }

        if ($verification->isExpired()) {
            return $this->errorResponse('Le code a expiré. Veuillez en demander un nouveau.', 422);
        }

        try {
            DB::transaction(function () use ($user, $verification): void {
                $verification->markAsVerified();
                $user->markEmailAsVerified();
                $user->emailVerifications()->delete();
            });

            return $this->successResponse(
                message: 'Adresse email vérifiée avec succès.',
            );
        } catch (Throwable $e) {
            Log::error('Erreur vérification email', [
                'user_id'   => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return $this->serverError('Erreur lors de la vérification. Veuillez réessayer.');
        }
    }

    /**
     * Renvoyer un code de vérification
     */
    public function resendVerifyCode(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(message: 'Votre adresse email est déjà vérifiée.');
        }

        $recentVerification = $user->emailVerifications()
            ->where('created_at', '>', now()->subMinute())
            ->first();

        if ($recentVerification) {
            return $this->tooManyRequests('Un code a déjà été envoyé récemment. Veuillez patienter.');
        }

        try {
            $code = DB::transaction(function () use ($user): string {
                $code = EmailVerification::generateCode();

                $user->emailVerifications()->create([
                    'code'       => $code,
                    'expires_at' => now()->addMinutes(10),
                ]);

                return $code;
            });

            // try {
            //     $this->mailService->send(
            //         $user->email,
            //         'emails.verify-code',
            //         'Vérification de votre adresse email',
            //         [
            //             'code' => $code,
            //             'user' => [
            //                 'first_name' => $user->first_name,
            //             ]
            //         ],
            //         false,
            //     );
            // } catch (Throwable $e) {
            //     Log::warning("Échec d'envoi de l'e-mail de vérification", [
            //         'email' => $user->email,
            //         'exception' => $e->getMessage(),
            //     ]);
            // }

            return $this->successResponse(
                message: 'Un nouveau code de vérification a été envoyé à votre adresse email.'
            );
        } catch (Throwable $e) {
            Log::error('Erreur renvoi code vérification', [
                'user_id'   => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return $this->serverError('Impossible d\'envoyer le code. Veuillez réessayer.');
        }
    }

    /**
     * Authentification d'un utilisateur existant
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return $this->unauthorized('Identifiants invalides.');
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            Auth::logout();
            return $this->forbidden('Votre adresse email n\'est pas encore vérifiée.');
        }

        $user->tokens()->each(fn($token) => $token->revoke());

        $tokenResult = $user->createToken('login');
        $accessToken = $tokenResult->accessToken;

        return $this->successResponse(
            message: 'Connexion réussie.',
            data: [
                'user' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->getFullNameAttribute(),
                    'email' => $user->email,
                ],
                'token' => $accessToken,
            ],
        );
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->unauthorized('Utilisateur non authentifié.');
            }

            $token = $user->token();

            if (!$token) {
                return $this->badRequest('Jeton d\'authentification introuvable.');
            }

            $token->revoke();

            return $this->successResponse(message: 'Déconnexion réussie.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la déconnexion', [
                'user_id'   => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return $this->serverError('Une erreur est survenue lors de la déconnexion. Veuillez réessayer plus tard.');
        }
    }

    /**
     * Demander un lien de réinitialisation de mot de passe
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if ($user) {
                $token = Password::createToken($user);
                $resetLink = route('api.v1.auth.password.reset', ['token' => $token, 'email' => $user->email]);

                $this->mailService->send(
                    $user->email,
                    'emails.password-reset',
                    'Réinitialisation de votre mot de passe',
                    [
                        'user' => [
                            'first_name' => $user->first_name
                        ],
                        'resetLink' => $resetLink,
                    ],
                    false,
                );
            }

            return $this->successResponse(
                message: 'Si cette adresse email est enregistrée, vous recevrez un lien de réinitialisation.'
            );
        } catch (Throwable $e) {
            Log::error('Erreur demande reset password', [
                'email'     => $request->email,
                'ip'        => $request->ip(),
                'exception' => $e->getMessage(),
            ]);

            return $this->serverError('Erreur lors de l\'envoi. Veuillez réessayer plus tard.');
        }
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password): void {
                    DB::transaction(function () use ($user, $password): void {
                        $user->forceFill([
                            'password'       => Hash::make($password),
                            'remember_token' => Str::random(60),
                        ])->save();
 
                        $user->tokens()->each(fn ($token) => $token->revoke());
 
                        event(new PasswordReset($user));
                    });
                }
            );
 
            if ($status === Password::PASSWORD_RESET) {
                return $this->successResponse(
                    message: 'Mot de passe réinitialisé avec succès. Veuillez vous connecter.'
                );
            }
 
            $errorMessages = [
                Password::INVALID_TOKEN => 'Ce lien de réinitialisation est invalide ou a expiré.',
                Password::INVALID_USER  => 'Aucun compte trouvé avec cette adresse email.',
                Password::RESET_THROTTLED => 'Trop de tentatives. Réessayez plus tard.',
            ];
 
            return $this->errorResponse($errorMessages[$status] ?? 'Erreur de réinitialisation.', 422);
        } catch (Throwable $e) {
            Log::error('Erreur reset password', [
                'email'     => $request->email,
                'ip'        => $request->ip(),
                'exception' => $e->getMessage(),
            ]);
 
            return $this->serverError('Erreur lors de la réinitialisation. Veuillez réessayer.');
        }
    }
}
