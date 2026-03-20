<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorie extends Model
{
    use HasUuids;

    protected $fillable = [
        'nom',
        'description',
        'icone',
        'ordre',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function profils(): HasMany
    {
        return $this->hasMany(Profil::class);
    }
}
