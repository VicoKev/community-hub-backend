<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'action_label' => $this->resolveActionLabel($this->action),
            'subject_type' => $this->subject_type
                ? class_basename($this->subject_type)
                : null,
            'subject_id' => $this->subject_id,
            'properties' => $this->properties,
            'ip_address' => $this->ip_address,
            'performed_by' => $this->user ? [
                'id' => $this->user->id,
                'email' => $this->user->email,
            ] : null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }

    private function resolveActionLabel(string $action): string
    {
        return match ($action) {
            'profile.approved' => 'Profil approuvé',
            'profile.rejected' => 'Profil rejeté',
            'user.deleted' => 'Utilisateur supprimé',
            'user.role_changed' => 'Rôle modifié',
            'export.profiles' => 'Export profils',
            'newsletter.sent' => 'Newsletter envoyée',
            default => $action,
        };
    }
}
