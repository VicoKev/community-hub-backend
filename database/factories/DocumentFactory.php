<?php

namespace Database\Factories;

use App\Models\Profil;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'profil_id'       => Profil::factory(),
            'type'            => fake()->randomElement(['cv', 'photo', 'doc_legal']),
            'nom_original'    => fake()->word() . '.pdf',
            'chemin_stockage' => 'documents/' . fake()->uuid() . '_cv_' . now()->timestamp . '.pdf',
            'taille_octets'   => fake()->numberBetween(10000, 1000000),
            'format_mime'     => 'application/pdf',
            'est_public'      => false,
        ];
    }
}
