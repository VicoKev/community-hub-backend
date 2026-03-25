<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Profile\SearchProfileRequest;
use App\Http\Requests\V1\Profile\StoreProfileRequest;
use App\Http\Requests\V1\Profile\UpdateProfileRequest;
use App\Http\Requests\V1\Profile\UploadFileRequest;
use App\Http\Resources\ProfileDetailResource;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Services\FileService;
use App\Services\ProfileService;
use App\Traits\JsonApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

final class ProfileController extends Controller
{
    use JsonApiResponse;

    public function __construct(
        private readonly ProfileService $profileService,
        private readonly FileService $fileService,
    ) {}

    /**
     * Recherche et filtrage des profils (annuaire public).
     */
    public function index(SearchProfileRequest $request): JsonResponse
    {
        $isAdmin = $request->user()?->hasAnyRole(['admin', 'super_admin', 'moderator']);
        $paginator = $this->profileService->search($request->validated(), $isAdmin);

        return $this->successResponse(
            data: ProfileResource::collection($paginator),
            message: 'Liste des profils récupérée avec succès.'
        );
    }

    /**
     * Profil détaillé
     */
    public function show(Profile $profile): JsonResponse
    {
        // Les non-admins ne peuvent voir que les profils approuvés
        if (! $profile->isApproved() && ! auth()->user()?->hasAnyRole(['admin', 'super_admin', 'moderator'])) {
            // L'owner peut voir son propre profil même en pending/rejected
            if (auth()->id() !== $profile->user_id) {
                return $this->notFound('Profil introuvable ou non encore approuvé.');
            }
        }

        $profile->loadMissing(['user:id,email', 'media']);

        return $this->successResponse(
            data: new ProfileDetailResource($profile),
            message: 'Profil récupéré avec succès.'
        );
    }

    /**
     * Voir son propre profil.
     */
    public function myProfile(Request $request): JsonResponse
    {
        $profile = $request->user()->profile()->with('media')->first();

        if (! $profile) {
            return $this->notFound('Vous n\'avez pas encore créé de profil.');
        }

        return $this->successResponse(
            data: new ProfileDetailResource($profile),
            message: 'Votre profil.'
        );
    }

    /**
     * Créer son profil.
     */
    public function store(StoreProfileRequest $request): JsonResponse
    {
        // Un user ne peut avoir qu'un seul profil
        if ($request->user()->profile()->exists()) {
            return $this->errorResponse(
                'Vous possédez déjà un profil. Utilisez la mise à jour.',
                409
            );
        }

        try {
            $profile = $this->profileService->createOrUpdate(
                $request->user(),
                $request->validated()
            );

            return $this->createdResponse(
                data: new ProfileDetailResource($profile->load('media')),
                message: 'Profil créé avec succès. Il sera visible après validation par un administrateur.'
            );
        } catch (Throwable $e) {
            return $this->serverError('Erreur lors de la création du profil.');
        }
    }

    /**
     * Modifier son profil.
     */
    public function update(UpdateProfileRequest $request, Profile $profile): JsonResponse
    {
        // Vérification ownership (ou admin)
        if ($profile->user_id !== $request->user()->id && ! $request->user()->hasAnyRole(['admin', 'super_admin'])) {
            return $this->forbidden('Vous ne pouvez modifier que votre propre profil.');
        }

        try {
            $updated = $this->profileService->createOrUpdate(
                $request->user(),
                $request->validated()
            );

            return $this->successResponse(
                data: new ProfileDetailResource($updated->load('media')),
                message: 'Profil mis à jour. Il sera revalidé par un administrateur.'
            );
        } catch (Throwable $e) {
            return $this->serverError('Erreur lors de la mise à jour du profil.');
        }
    }

    /**
     * Supprimer son profil.
     */
    public function destroy(Request $request, Profile $profile): JsonResponse
    {
        if ($profile->user_id !== $request->user()->id && ! $request->user()->hasAnyRole(['admin', 'super_admin'])) {
            return $this->forbidden('Vous ne pouvez supprimer que votre propre profil.');
        }

        $profile->delete();

        return $this->noContentResponse();
    }

    /**
     * Uploader un ou plusieurs fichiers sur un profil.
     */
    public function uploadFile(UploadFileRequest $request, Profile $profile): JsonResponse
    {
        if ($profile->user_id !== $request->user()->id && ! $request->user()->hasAnyRole(['admin', 'super_admin'])) {
            return $this->forbidden('Accès non autorisé.');
        }

        $collection = $request->input('collection');

        try {
            if ($request->hasFile('file')) {
                // Upload (avatar ou cv)
                $media = $this->fileService->upload($profile, $request->file('file'), $collection);
                $files = [$this->mediaToArray($media)];
            } else {
                // Upload multiple (documents)
                $uploaded = $this->fileService->uploadMultiple($profile, $request->file('files'), $collection);
                $files = array_map([$this, 'mediaToArray'], $uploaded);
            }

            return $this->successResponse(
                data: ['files' => $files, 'collection' => $collection],
                message: 'Fichier(s) uploadé(s) avec succès.'
            );
        } catch (InvalidArgumentException $e) {
            return $this->badRequest($e->getMessage());
        } catch (Throwable $e) {
            return $this->serverError('Erreur lors de l\'upload.');
        }
    }

    /**
     * Supprimer un fichier.
     */
    public function deleteFile(Request $request, Profile $profile, int $mediaId): JsonResponse
    {
        if ($profile->user_id !== $request->user()->id && ! $request->user()->hasAnyRole(['admin', 'super_admin'])) {
            return $this->forbidden('Accès non autorisé.');
        }

        try {
            $this->fileService->delete($profile, $mediaId);

            return $this->noContentResponse();
        } catch (Throwable $e) {
            return $this->notFound('Fichier introuvable.');
        }
    }

    private function mediaToArray(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): array
    {
        return [
            'id' => $media->id,
            'name' => $media->name,
            'file_name' => $media->file_name,
            'url' => $media->getFullUrl(),
            'thumb_url' => $media->hasGeneratedConversion('thumb') ? $media->getFullUrl('thumb') : null,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'created_at' => $media->created_at->toIso8601String(),
        ];
    }
}
