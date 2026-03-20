<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UploadDocumentRequest;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $profil = $user->profil()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'categorie_id'      => $request->categorieId,
                'bio'               => $request->bio,
                'localisation'      => $request->localisation,
                'quartier'          => $request->quartier,
                'arrondissement'    => $request->arrondissement,
                'site_web'          => $request->siteWeb,
                'reseaux_sociaux'   => $request->reseauxSociaux,
                'niveau_etude'      => $request->niveauEtude,
                'secteur_activite'  => $request->secteurActivite,
                'metier'            => $request->metier,
                'competences'       => $request->competences,
                'visibilite_contact' => $request->visibiliteContact ?? 'PRIVE',
            ]
        );

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'profil'  => $profil,
        ]);
    }

    public function upload(UploadDocumentRequest $request): JsonResponse
    {
        $user   = $request->user();
        $profil = $user->profil()->firstOrCreate(['user_id' => $user->id]);

        $fichier    = $request->file('fichier');
        $type       = $request->type;
        $timestamp  = now()->timestamp;
        $extension  = $fichier->getClientOriginalExtension();
        $nomFichier = "{$user->id}_{$type}_{$timestamp}.{$extension}";

        $chemin = $fichier->storeAs('documents', $nomFichier);

        $document = Document::create([
            'profil_id'        => $profil->id,
            'type'             => $type,
            'nom_original'     => $fichier->getClientOriginalName(),
            'chemin_stockage'  => $chemin,
            'taille_octets'    => $fichier->getSize(),
            'format_mime'      => $fichier->getMimeType(),
            'est_public'       => false,
        ]);

        return response()->json([
            'message'  => 'Document uploadé avec succès.',
            'document' => [
                'id'          => $document->id,
                'type'        => $document->type,
                'nomOriginal' => $document->nom_original,
                'taille'      => $document->taille_octets,
                'format'      => $document->format_mime,
            ],
        ], 201);
    }
}
