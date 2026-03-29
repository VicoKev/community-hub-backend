<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProfileStatus;
use App\Models\Profile;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ProfileService
{
    public function __construct(
        private MailService $mailService
    ) {}

    /**
     * Créer ou mettre à jour le profil d'un utilisateur.
     */
    public function createOrUpdate(User $user, array $data): Profile
    {
        return DB::transaction(function () use ($user, $data): Profile {
            $profile = Profile::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($data, [
                    'status' => ProfileStatus::PENDING,
                ])
            );

            if (! $user->hasRole('user')) {
                $user->assignRole('user');
            }

            Log::info('Profil créé/mis à jour', [
                'user_id' => $user->id,
                'profile_id' => $profile->id,
                'action' => $profile->wasRecentlyCreated ? 'created' : 'updated',
            ]);

            // Notifier les admins d'un nouveau profil à valider
            if ($profile->wasRecentlyCreated) {
                $profile = $profile->loadMissing('user');
                $user = $profile->user;

                $admins = User::role(['admin'])->get();

                if ($admins->isNotEmpty()) {
                    $adminEmails = $admins->pluck('email')->toArray();

                    $this->mailService->send(
                        to: User::role(['super_admin'])->first(),
                        view: 'emails.admin.new-profile',
                        subject: 'Nouveau profil à valider — ' . $user->full_name,
                        data: [
                            'profile' => $profile,
                            'user' => $user,
                        ],
                        useQueue: true,
                        bcc: $adminEmails,
                    );
                }
            }

            return $profile->fresh();
        });
    }

    /**
     * Approuver un profil.
     */
    public function approve(Profile $profile, User $admin): Profile
    {
        if ($profile->isApproved()) {
            return $profile;
        }

        DB::transaction(function () use ($profile, $admin): void {
            $profile->update([
                'status' => ProfileStatus::APPROVED,
                'validated_by' => $admin->id,
                'validated_at' => now(),
                'rejection_reason' => null,
            ]);
        });

        // Envoyer la notification d'approbation à l'utilisateur
        $profile = $profile->loadMissing('user');
        $user = $profile->user;

        if ($user) {
            $this->mailService->send(
                to: $user,
                view: 'emails.profile.approved',
                subject: 'Votre profil a été approuvé !',
                data: [
                    'profile' => $profile,
                    'user' => $user,
                ],
                useQueue: true,
            );
        }

        ActivityLogService::log($admin, 'profile.approved', $profile, [
            'profile_owner' => $profile->user_id,
        ]);

        return $profile->fresh();
    }

    /**
     * Rejeter un profil avec une raison.
     */
    public function reject(Profile $profile, User $admin, string $reason): Profile
    {
        DB::transaction(function () use ($profile, $admin, $reason): void {
            $profile->update([
                'status' => ProfileStatus::REJECTED,
                'validated_by' => $admin->id,
                'validated_at' => now(),
                'rejection_reason' => $reason,
            ]);
        });

        // Envoyer la notification de rejet à l'utilisateur
        $profile = $profile->loadMissing('user');
        $user = $profile->user;

        if ($user) {
            $this->mailService->send(
                to: $user,
                view: 'emails.profile.rejected',
                subject: 'Votre profil nécessite des corrections',
                data: [
                    'profile' => $profile,
                    'user' => $user,
                    'reason' => $reason,
                ],
                useQueue: true,
            );
        }

        ActivityLogService::log($admin, 'profile.rejected', $profile, [
            'reason' => $reason,
        ]);

        return $profile->fresh();
    }

    /**
     * Recherche avancée avec filtres multiples.
     * Retourne uniquement les profils approuvés pour les utilisateurs non-admin.
     */
    public function search(array $filters, bool $adminMode = false): LengthAwarePaginator
    {
        $query = Profile::query()
            ->with(['user:id,email', 'media']);

        // Filtre statut
        if (! $adminMode) {
            $query->approved();
        } elseif (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Recherche textuelle
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filtres exacts
        $exactFilters = ['category', 'sector', 'education_level', 'city', 'commune'];
        foreach ($exactFilters as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        // Tri
        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at', 'sector'])
            ? $filters['sort_by']
            : 'created_at';
        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir);

        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        return $query->paginate($perPage);
    }

    /**
     * Statistiques globales pour le dashboard admin.
     */
    public function getStatistics(): array
    {
        return [
            'total' => Profile::count(),
            'approved' => Profile::where('status', ProfileStatus::APPROVED)->count(),
            'pending' => Profile::where('status', ProfileStatus::PENDING)->count(),
            'rejected' => Profile::where('status', ProfileStatus::REJECTED)->count(),
            'by_category' => Profile::approved()
                ->selectRaw('category, count(*) as total')
                ->groupBy('category')
                ->pluck('total', 'category'),
            'by_sector' => Profile::approved()
                ->whereNotNull('sector')
                ->selectRaw('sector, count(*) as total')
                ->groupBy('sector')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'sector'),
            'by_education_level' => Profile::approved()
                ->whereNotNull('education_level')
                ->selectRaw('education_level, count(*) as total')
                ->groupBy('education_level')
                ->pluck('total', 'education_level'),
            'by_city' => Profile::approved()
                ->whereNotNull('city')
                ->selectRaw('city, count(*) as total')
                ->groupBy('city')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'city'),
            'monthly_registrations' => Profile::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as total')
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month'),
        ];
    }
}
