<?php

use App\Models\Categorie;
use App\Models\User;
use Laravel\Passport\Passport;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->categorie = Categorie::factory()->create();
    $this->user = User::factory()->create([
        'statut' => 'valide',
        'actif'  => true,
        'role'   => 'member',
    ]);
});

// --- PUT /api/profile/me ---

it('crée le profil si inexistant', function () {
    Passport::actingAs($this->user);

    $response = $this->putJson('/api/profile/me', [
        'niveauEtude'       => 'Master',
        'visibiliteContact' => 'PUBLIC',
        'categorieId'       => $this->categorie->id,
    ]);

    $response->assertStatus(200)
             ->assertJsonPath('profil.niveau_etude', 'Master');

    $this->assertDatabaseHas('profils', ['user_id' => $this->user->id]);
});

it('met à jour le profil existant', function () {
    Passport::actingAs($this->user);

    $this->putJson('/api/profile/me', ['niveauEtude' => 'Licence']);
    $response = $this->putJson('/api/profile/me', ['niveauEtude' => 'Doctorat']);

    $response->assertStatus(200)
             ->assertJsonPath('profil.niveau_etude', 'Doctorat');

    $this->assertDatabaseCount('profils', 1);
});

it('rejette un niveauEtude invalide', function () {
    Passport::actingAs($this->user);

    $this->putJson('/api/profile/me', ['niveauEtude' => 'Brevet'])
         ->assertStatus(422)
         ->assertJsonValidationErrors(['niveauEtude']);
});

it('rejette un visibiliteContact invalide', function () {
    Passport::actingAs($this->user);

    $this->putJson('/api/profile/me', ['visibiliteContact' => 'PARTAGE'])
         ->assertStatus(422)
         ->assertJsonValidationErrors(['visibiliteContact']);
});

it('bloque un utilisateur non authentifié', function () {
    $this->putJson('/api/profile/me', [])->assertStatus(401);
});
