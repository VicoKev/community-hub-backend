<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProfileResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name.' '.$this->last_name,
            'category' => $this->category,
            'category_label' => $this->category_label,
            'sector' => $this->sector,
            'profession' => $this->profession,
            'company_name' => $this->company_name,
            'city' => $this->city,
            'commune' => $this->commune,
            'education_level' => $this->education_level,
            'status' => $this->status,
            'avatar_url' => $this->avatar_url,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
