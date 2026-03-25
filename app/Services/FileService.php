<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class FileService
{
    /**
     * Configuration des collections autorisées par modèle.
     */
    private const COLLECTION_CONFIG = [
        'avatar' => ['max_kb' => 2048,  'mimes' => ['image/jpeg', 'image/png', 'image/webp']],
        'cv' => ['max_kb' => 5120,  'mimes' => ['application/pdf']],
        'documents' => ['max_kb' => 5120,  'mimes' => ['application/pdf', 'image/jpeg', 'image/png']],
    ];

    /**
     * Uploader un fichier dans une collection donnée.
     */
    public function upload(
        HasMedia $model,
        UploadedFile $file,
        string $collection,
        ?string $customName = null
    ): Media {
        $this->validateCollection($collection);

        $adder = $model->addMedia($file)
            ->usingName($customName ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            ->usingFileName($this->sanitizeFileName($file));

        $media = $adder->toMediaCollection($collection);

        Log::info('Fichier uploadé', [
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'collection' => $collection,
            'media_id' => $media->id,
            'file_name' => $media->file_name,
            'size' => $media->size,
        ]);

        return $media;
    }

    /**
     * Uploader plusieurs fichiers dans une collection.
     */
    public function uploadMultiple(
        HasMedia $model,
        array $files,
        string $collection
    ): array {
        $uploaded = [];

        foreach ($files as $file) {
            $uploaded[] = $this->upload($model, $file, $collection);
        }

        return $uploaded;
    }

    /**
     * Supprimer un media par son ID (avec vérification d'ownership).
     */
    public function delete(HasMedia $model, int $mediaId): bool
    {
        $media = $model->media()->findOrFail($mediaId);
        $media->delete();

        Log::info('Fichier supprimé', [
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'media_id' => $mediaId,
        ]);

        return true;
    }

    /**
     * Vider entièrement une collection.
     */
    public function clearCollection(HasMedia $model, string $collection): void
    {
        $model->clearMediaCollection($collection);

        Log::info('Collection vidée', [
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'collection' => $collection,
        ]);
    }

    /**
     * Retourne les métadonnées des fichiers d'une collection.
     */
    public function getCollectionFiles(HasMedia $model, string $collection): array
    {
        return $model->getMedia($collection)->map(fn (Media $media): array => [
            'id' => $media->id,
            'name' => $media->name,
            'file_name' => $media->file_name,
            'url' => $media->getFullUrl(),
            'thumb_url' => $media->hasGeneratedConversion('thumb')
                                ? $media->getFullUrl('thumb')
                                : null,
            'size' => $media->size,
            'size_human' => $this->formatSize($media->size),
            'mime_type' => $media->mime_type,
            'created_at' => $media->created_at->toIso8601String(),
        ])->values()->toArray();
    }

    /**
     * Retourne toutes les collections d'un modèle.
     */
    public function getAllFiles(HasMedia $model): array
    {
        $result = [];

        foreach (array_keys(self::COLLECTION_CONFIG) as $collection) {
            $files = $this->getCollectionFiles($model, $collection);
            if (! empty($files)) {
                $result[$collection] = $files;
            }
        }

        return $result;
    }

    private function validateCollection(string $collection): void
    {
        if (! isset(self::COLLECTION_CONFIG[$collection])) {
            throw new InvalidArgumentException(
                "Collection '{$collection}' non autorisée. Collections valides : "
                .implode(', ', array_keys(self::COLLECTION_CONFIG))
            );
        }
    }

    private function sanitizeFileName(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        // Supprimer les caractères spéciaux, remplacer espaces par tirets
        $clean = preg_replace('/[^a-zA-Z0-9\-_]/', '', str_replace(' ', '-', $name));
        $clean = mb_strtolower(trim($clean, '-'));

        return ($clean ?: 'file').'_'.time().'.'.$extension;
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 1).' MB';
    }
}
