<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profil>
 */
class ProfilFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'statut'            => 'en_attente',
            'visibilite_contact' => 'PRIVE',
        ];
    }
}
