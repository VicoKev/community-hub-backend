<?php

use App\Models\Categorie;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    $this->categorie = Categorie::factory()->create();
    $this->user = User::factory()->create([
        'statut' => 'valide',
        'actif'  => true,
    ]);
});

it('uploade un CV PDF valide', function () {
    Passport::actingAs($this->user);

    $fichier = UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf');

    $this->postJson('/api/profile/upload', [
        'type'    => 'cv',
        'fichier' => $fichier,
    ])->assertStatus(201)
      ->assertJsonPath('document.type', 'cv');

    $this->assertDatabaseHas('documents', [
        'type'       => 'cv',
        'format_mime' => 'application/pdf',
    ]);
});

it('uploade une photo PNG valide', function () {
    Passport::actingAs($this->user);

    $fichier = UploadedFile::fake()->image('photo.png', 100, 100);

    $this->postJson('/api/profile/upload', [
        'type'    => 'photo',
        'fichier' => $fichier,
    ])->assertStatus(201)
      ->assertJsonPath('document.type', 'photo');
});

it('rejette une photo trop lourde', function () {
    Passport::actingAs($this->user);

    $fichier = UploadedFile::fake()->create('photo.jpg', 1200, 'image/jpeg');

    $this->postJson('/api/profile/upload', [
        'type'    => 'photo',
        'fichier' => $fichier,
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['fichier']);
});

it('rejette un PDF trop lourd', function () {
    Passport::actingAs($this->user);

    $fichier = UploadedFile::fake()->create('doc.pdf', 2200, 'application/pdf');

    $this->postJson('/api/profile/upload', [
        'type'    => 'cv',
        'fichier' => $fichier,
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['fichier']);
});

it('bloque un utilisateur non authentifié', function () {
    $this->postJson('/api/profile/upload', [])->assertStatus(401);
});
