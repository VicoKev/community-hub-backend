<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Seeder;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nom' => 'Cadres administratifs',    'ordre' => 1],
            ['nom' => 'Cadres techniques',        'ordre' => 2],
            ['nom' => "Chefs d'entreprise",       'ordre' => 3],
            ['nom' => 'Artisans',                 'ordre' => 4],
            ['nom' => 'Commerçants',              'ordre' => 5],
            ['nom' => 'Jeunes entrepreneurs',     'ordre' => 6],
            ['nom' => 'Investisseurs / Partenaires', 'ordre' => 7],
        ];

        foreach ($categories as $categorie) {
            Categorie::create($categorie);
        }
    }
}
