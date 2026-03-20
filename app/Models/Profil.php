<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profil extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'categorie_id',
        'bio',
        'localisation',
        'quartier',
        'arrondissement',
        'site_web',
        'reseaux_sociaux',
        'niveau_etude',
        'secteur_activite',
        'metier',
        'competences',
        'visibilite_contact',
        'statut',
        'motif_rejet',
        'date_validation',
    ];

    protected function casts(): array
    {
        return [
            'competences'     => 'array',
            'reseaux_sociaux' => 'array',
            'date_validation' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
