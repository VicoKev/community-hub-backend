<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileStatusRequest;
use App\Mail\ProfilRejete;
use App\Mail\ProfilValide;
use App\Models\Profil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class ProfileController extends Controller
{
    public function updateStatus(UpdateProfileStatusRequest $request, string $id): JsonResponse
    {
        $profil = Profil::with(['user', 'documents'])->findOrFail($id);

        $statut     = $request->statut;
        $motifRejet = $request->motifRejet;

        // Bloquer la validation si aucun document n'a été uploadé
        if ($statut === 'valide' && $profil->documents->isEmpty()) {
            return response()->json([
                'message' => 'Impossible de valider ce profil : aucun document n\'a été soumis par le membre.',
            ], 422);
        }

        // suspendu = statut compte uniquement, pas statut profil
        // profils.statut : en_attente, valide, rejete
        // users.statut   : en_attente, valide, rejete, suspendu
        if ($statut !== 'suspendu') {
            $profil->update([
                'statut'          => $statut,
                'motif_rejet'     => $statut === 'rejete' ? $motifRejet : null,
                'date_validation' => $statut === 'valide' ? now() : null,
            ]);
        }

        // Synchroniser le statut du compte utilisateur
        $profil->user->update([
            'statut' => $statut,
            'actif'  => $statut === 'valide',
        ]);

        // Envoyer le mail de notification
        if ($statut === 'valide') {
            Mail::to($profil->user->email)->send(new ProfilValide($profil->user));
        } else {
            Mail::to($profil->user->email)->send(new ProfilRejete($profil->user, $statut, $motifRejet));
        }

        return response()->json([
            'message' => "Profil $statut avec succès.",
            'profil'  => [
                'id'             => $profil->id,
                'statut'         => $profil->statut,
                'dateValidation' => $profil->date_validation,
                'motifRejet'     => $profil->motif_rejet,
            ],
        ]);
    }
}
