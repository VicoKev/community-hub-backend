<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProfileDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $isOwner = $viewer && $viewer->id === $this->user_id;
        $isAdmin = $viewer && $viewer->hasAnyRole(['admin', 'super_admin', 'moderator']);

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name.' '.$this->last_name,
            'bio' => $this->bio,

            'category' => $this->category,
            'category_label' => $this->category_label,
            'sector' => $this->sector,
            'profession' => $this->profession,
            'company_name' => $this->company_name,

            'city' => $this->city,
            'commune' => $this->commune,

            'education_level' => $this->education_level,
            'institution' => $this->institution,
            'field_of_study' => $this->field_of_study,
            'graduation_year' => $this->graduation_year,

            'skills' => $this->skills ?? [],
            'languages' => $this->languages ?? [],
            'years_of_experience' => $this->years_of_experience,
            'experiences' => $this->experiences ?? [],

            'email' => $this->show_email || $isOwner || $isAdmin
                                    ? $this->user?->email
                                    : null,
            'phone' => $this->show_phone || $isOwner || $isAdmin
                                    ? $this->phone
                                    : null,
            'website' => $this->website,
            'linkedin' => $this->linkedin,

            'avatar_url' => $this->avatar_url,
            'files' => $this->when(
                $isOwner || $isAdmin,
                fn () => [
                    'avatar' => $this->getMedia('avatar')->map(fn ($m) => [
                        'id' => $m->id,
                        'url' => $m->getFullUrl(),
                        'thumb' => $m->getFullUrl('thumb'),
                    ])->first(),
                    'cv' => $this->getMedia('cv')->map(fn ($m) => [
                        'id' => $m->id,
                        'url' => $m->getFullUrl(),
                        'name' => $m->file_name,
                    ])->first(),
                    'documents' => $this->getMedia('documents')->map(fn ($m) => [
                        'id' => $m->id,
                        'url' => $m->getFullUrl(),
                        'name' => $m->file_name,
                        'mime' => $m->mime_type,
                    ])->values(),
                ]
            ),

            'status' => $this->status,
            'rejection_reason' => $this->when($isOwner || $isAdmin, $this->rejection_reason),
            'validated_at' => $this->when($isAdmin, $this->validated_at?->toIso8601String()),
            'validated_by' => $this->when($isAdmin, $this->validatedBy?->email),

            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
