<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Profile\ValidateProfileRequest;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\ProfileDetailResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\Profile;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\ProfileService;
use App\Traits\JsonApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class AdminController extends Controller
{
    use JsonApiResponse;

    public function __construct(
        private readonly ProfileService $profileService,
    ) {}

    /**
     * Statistiques globales pour le dashboard.
     */
    public function stats(): JsonResponse
    {
        $stats = $this->profileService->getStatistics();

        // Enrichir avec stats utilisateurs
        $stats['users'] = [
            'total' => User::count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'with_profile' => User::has('profile')->count(),
        ];

        return $this->successResponse(
            data: $stats,
            message: 'Statistiques récupérées avec succès.'
        );
    }

    /**
     * Liste des profils.
     */
    public function profiles(Request $request): JsonResponse
    {
        $query = Profile::query()
            ->with(['user:id,email', 'media'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $profiles = $query->paginate($perPage);

        return $this->successResponse(
            data: ProfileResource::collection($profiles),
            message: 'Profils récupérés.'
        );
    }

    /**
     * Approuver un profil.
     */
    public function approveProfile(Profile $profile): JsonResponse
    {
        if ($profile->isApproved()) {
            return $this->errorResponse('Ce profil est déjà approuvé.', 422);
        }

        try {
            $updated = $this->profileService->approve($profile, auth()->user());

            return $this->successResponse(
                data: new ProfileDetailResource($updated),
                message: 'Profil approuvé avec succès.'
            );
        } catch (Throwable $e) {
            return $this->serverError('Erreur lors de la validation.');
        }
    }

    /**
     * Rejeter un profil avec une raison.
     */
    public function rejectProfile(ValidateProfileRequest $request, Profile $profile): JsonResponse
    {
        try {
            $updated = $this->profileService->reject(
                $profile,
                auth()->user(),
                $request->validated('reason')
            );

            return $this->successResponse(
                data: new ProfileDetailResource($updated),
                message: 'Profil rejeté.'
            );
        } catch (Throwable $e) {
            return $this->serverError('Erreur lors du rejet.');
        }
    }

    /**
     * Liste des utilisateurs.
     */
    public function users(Request $request): JsonResponse
    {
        $users = User::query()
            ->with(['roles', 'profile:id,user_id,status,category'])
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request): void {
                $q->where('email', 'like', "%{$request->search}%");
            }))
            ->when($request->filled('role'), fn ($q) => $q->role($request->role))
            ->orderBy('created_at', 'desc')
            ->paginate(min((int) $request->get('per_page', 20), 100));

        return $this->successResponse(
            data: UserResource::collection($users),
            message: 'Utilisateurs récupérés.'
        );
    }

    /**
     * Modifier le rôle d'un utilisateur.
     */
    public function updateUserRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => ['required', 'string', 'in:user,moderator,admin'],
        ]);

        // Empêcher de modifier un super_admin
        if ($user->hasRole('super_admin')) {
            return $this->forbidden('Impossible de modifier le rôle d\'un super administrateur.');
        }

        $user->syncRoles([$request->role]);

        ActivityLogService::log(auth()->user(), 'user.role_changed', $user, [
            'new_role' => $request->role,
        ]);

        return $this->successResponse(
            data: new UserResource($user->load('roles')),
            message: 'Rôle mis à jour.'
        );
    }

    /**
     * Supprimer un utilisateur.
     */
    public function deleteUser(User $user): JsonResponse
    {
        if ($user->hasRole('super_admin')) {
            return $this->forbidden('Impossible de supprimer un super administrateur.');
        }

        ActivityLogService::log(auth()->user(), 'user.deleted', $user, [
            'email' => $user->email,
        ]);

        $user->delete();

        return $this->noContentResponse();
    }

    /**
     * Exporter les profils en Excel ou CSV.
     */
    // public function exportProfiles(Request $request)
    // {
    //     $request->validate([
    //         'format' => ['nullable', 'in:xlsx,csv'],
    //         'status' => ['nullable', 'in:pending,approved,rejected'],
    //     ]);

    //     $format = $request->get('format', 'xlsx');
    //     $status = $request->get('status');
    //     $filename = 'profiles_'.now()->format('Y-m-d_His').'.'.$format;

    //     ActivityLogService::log(auth()->user(), 'export.profiles', null, [
    //         'format' => $format,
    //         'status' => $status,
    //     ]);

    //     return Excel::download(new ProfilesExport($status), $filename);
    // }

    /**
     * Consulter les logs d'activité administrateur.
     */
    public function activityLogs(Request $request): JsonResponse
    {
        $logs = ActivityLog::query()
            ->with('user:id,email')
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', "%{$request->action}%"))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->orderBy('created_at', 'desc')
            ->paginate(min((int) $request->get('per_page', 50), 200));

        return $this->successResponse(
            data: ActivityLogResource::collection($logs),
            message: 'Logs récupérés.'
        );
    }
}
