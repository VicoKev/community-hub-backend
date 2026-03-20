<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileStatusRequest;
use App\Mail\ProfilRejete;
use App\Mail\ProfilValide;
use App\Models\Profil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProfileController extends Controller
{
    public function updateStatus(UpdateProfileStatusRequest $request, string $id): JsonResponse
    {
        $profil = Profil::with(['user', 'documents'])->findOrFail($id);

        $statut     = $request->statut;
        $motifRejet = $request->motifRejet;

        if ($statut === 'valide' && $profil->documents->isEmpty()) {
            return response()->json([
                'message' => "Impossible de valider ce profil : aucun document n'a été soumis par le membre.",
            ], 422);
        }

        DB::transaction(function () use ($profil, $statut, $motifRejet) {
            $profil->update([
                'statut'          => $statut,
                'motif_rejet'     => in_array($statut, ['rejete', 'suspendu']) ? $motifRejet : null,
                'date_validation' => $statut === 'valide' ? now() : null,
            ]);

            $profil->user->update([
                'statut' => $statut,
                'actif'  => $statut === 'valide',
            ]);
        });

        // Mail hors transaction : un échec d'envoi ne doit pas rollback la DB
        try {
            if ($statut === 'valide') {
                Mail::to($profil->user->email)->send(new ProfilValide($profil->user));
            } else {
                Mail::to($profil->user->email)->send(new ProfilRejete($profil->user, $statut, $motifRejet));
            }
        } catch (\Throwable $e) {
            Log::error("Échec envoi mail modération profil {$profil->id} : {$e->getMessage()}");
        }

        $profil->refresh();

        return response()->json([
            'message' => "Profil {$statut} avec succès.",
            'profil'  => [
                'id'             => $profil->id,
                'statut'         => $profil->statut,
                'dateValidation' => $profil->date_validation,
                'motifRejet'     => $profil->motif_rejet,
            ],
        ]);
    }
}
