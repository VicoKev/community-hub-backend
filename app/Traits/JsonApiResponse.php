<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

trait JsonApiResponse
{
    /**
     * Réponse de succès (200 ou 201).
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Opération réussie.',
        int $status = 200
    ): JsonResponse {
        $payload = [
            'status'  => 'success',
            'message' => $message,
        ];
 
        // Pagination (LengthAwarePaginator, Paginator…)
        if ($data instanceof AbstractPaginator) {
            $payload['data']  = $data->items();
            $payload['meta']  = $this->buildMeta($data);
            $payload['links'] = $this->buildLinks($data);
 
            return response()->json($payload, $status);
        }
 
        // Laravel API Resource Collection
        if ($data instanceof ResourceCollection) {
            $resource = $data->response()->getData(true);
 
            $payload['data'] = $resource['data'] ?? [];
 
            if (isset($resource['meta'])) {
                $payload['meta']  = $resource['meta'];
                $payload['links'] = $resource['links'] ?? null;
            }
 
            return response()->json($payload, $status);
        }
 
        // Laravel API Resource (single)
        if ($data instanceof JsonResource) {
            $payload['data'] = $data->response()->getData(true)['data'] ?? $data;
 
            return response()->json($payload, $status);
        }
 
        // Données brutes (array, null, scalar…)
        $payload['data'] = $data;
 
        return response()->json($payload, $status);
    }
 
    /**
     * Réponse 201 Created.
     */
    protected function createdResponse(
        mixed $data = null,
        string $message = 'Ressource créée avec succès.'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }
 
    /**
     * Réponse 204 No Content.
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }
 
  
 
    /**
     * Réponse d'erreur.
     */
    protected function errorResponse(
        string $message = 'Une erreur est survenue.',
        int $status = 500,
        array $errors = []
    ): JsonResponse {
        $payload = [
            'status'  => 'error',
            'message' => $message,
            'data'    => null,
        ];
 
        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }
 
        return response()->json($payload, $status);
    }
 
    /**
     * 422 — Erreurs de validation.
     */
    protected function validationError(
        array|object $errors,
        string $message = 'Les données fournies sont invalides.'
    ): JsonResponse {
        $formattedErrors = is_object($errors) && method_exists($errors, 'toArray')
            ? $errors->toArray()
            : (array) $errors;
 
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'data'    => null,
            'errors'  => $formattedErrors,
        ], 422);
    }
 
    /**
     * 401 — Non authentifié.
     */
    protected function unauthorized(
        string $message = 'Non authentifié. Veuillez vous connecter.'
    ): JsonResponse {
        return $this->errorResponse($message, 401);
    }
 
    /**
     * 403 — Accès interdit.
     */
    protected function forbidden(
        string $message = 'Vous n\'êtes pas autorisé à effectuer cette action.'
    ): JsonResponse {
        return $this->errorResponse($message, 403);
    }
 
    /**
     * 404 — Ressource introuvable.
     */
    protected function notFound(
        string $message = 'La ressource demandée est introuvable.'
    ): JsonResponse {
        return $this->errorResponse($message, 404);
    }
 
    /**
     * 400 — Mauvaise requête.
     */
    protected function badRequest(
        string $message = 'Requête invalide.',
        array $errors = []
    ): JsonResponse {
        return $this->errorResponse($message, 400, $errors);
    }
 
    /**
     * 429 — Trop de requêtes.
     */
    protected function tooManyRequests(
        string $message = 'Trop de tentatives. Veuillez réessayer plus tard.'
    ): JsonResponse {
        return $this->errorResponse($message, 429);
    }
 
    /**
     * 500 — Erreur serveur interne.
     */
    protected function serverError(
        string $message = 'Une erreur interne est survenue.'
    ): JsonResponse {
        return $this->errorResponse($message, 500);
    }
 
    

    /**
     * Construit le bloc "meta" pour un paginator.
     */
    private function buildMeta(AbstractPaginator $paginator): array
    {
        $meta = [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'path'         => $paginator->path(),
        ];
 
        // LengthAwarePaginator expose total() et lastPage()
        if (method_exists($paginator, 'total')) {
            $meta['total']      = $paginator->total();
            $meta['last_page']  = $paginator->lastPage();
        }
 
        return $meta;
    }
 
    /**
     * Construit le bloc "links" pour un paginator.
     */
    private function buildLinks(AbstractPaginator $paginator): array
    {
        return [
            'first' => $paginator->url(1),
            'last'  => method_exists($paginator, 'lastPage')
                        ? $paginator->url($paginator->lastPage())
                        : null,
            'prev'  => $paginator->previousPageUrl(),
            'next'  => $paginator->nextPageUrl(),
        ];
    }
}
