<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'profil_id',
        'type',
        'nom_original',
        'chemin_stockage',
        'taille_octets',
        'format_mime',
        'est_public',
    ];

    protected function casts(): array
    {
        return [
            'est_public' => 'boolean',
        ];
    }

    public function profil(): BelongsTo
    {
        return $this->belongsTo(Profil::class);
    }

    public function getUrl(): ?string
    {
        if (! $this->est_public) {
            return null;
        }

        return Storage::url($this->chemin_stockage);
    }
}
