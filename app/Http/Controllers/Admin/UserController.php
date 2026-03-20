<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Un admin ne peut pas supprimer son propre compte
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 403);
        }

        // Supprimer les fichiers physiques
        if ($user->profil) {
            foreach ($user->profil->documents as $document) {
                Storage::delete($document->chemin_stockage);
            }
        }

        // Suppression en cascade (profil + documents via DB cascade)
        $user->delete();

        return response()->json(null, 204);
    }
}
