<?php

use App\Models\Categorie;
use App\Models\Document;
use App\Models\Profil;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Storage::fake('local');

    $this->categorie = Categorie::factory()->create();

    $this->admin = User::factory()->create([
        'statut' => 'valide',
        'actif'  => true,
        'role'   => 'admin',
    ]);

    $this->membre = User::factory()->create([
        'statut' => 'en_attente',
        'actif'  => false,
        'role'   => 'member',
    ]);

    $this->profil = Profil::factory()->create([
        'user_id'    => $this->membre->id,
        'statut'     => 'en_attente',
    ]);
});

// --- Accès ---

it('bloque un membre sans rôle admin', function () {
    Passport::actingAs($this->membre);

    $this->patchJson("/api/admin/profiles/{$this->profil->id}/status", [
        'statut' => 'valide',
    ])->assertStatus(403);
});

// --- Validation ---

it('bloque la validation si aucun document', function () {
    Passport::actingAs($this->admin);

    $this->patchJson("/api/admin/profiles/{$this->profil->id}/status", [
        'statut' => 'valide',
    ])->assertStatus(422)
      ->assertJsonFragment(['message' => "Impossible de valider ce profil : aucun document n'a été soumis par le membre."]);
});

it('valide le profil avec document — synchronise user et envoie mail', function () {
    Passport::actingAs($this->admin);

    Document::factory()->create(['profil_id' => $this->profil->id]);

    $this->patchJson("/api/admin/profiles/{$this->profil->id}/status", [
        'statut' => 'valide',
    ])->assertStatus(200)
      ->assertJsonPath('profil.statut', 'valide');

    $this->assertDatabaseHas('profils', ['id' => $this->profil->id, 'statut' => 'valide']);
    $this->assertDatabaseHas('users',   ['id' => $this->membre->id, 'statut' => 'valide', 'actif' => 1]);

    Mail::assertSent(\App\Mail\ProfilValide::class, fn ($mail) => $mail->hasTo($this->membre->email));
});

// --- Rejet ---

it('exige un motif pour rejeter', function () {
    Passport::actingAs($this->admin);

    $this->patchJson("/api/admin/profiles/{$this->profil->id}/status", [
        'statut' => 'rejete',
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['motifRejet']);
});

it('rejette le profil avec motif — synchronise user et envoie mail', function () {
    Passport::actingAs($this->admin);

    $this->patchJson("/api/admin/profiles/{$this->profil->id}/status", [
        'statut'     => 'rejete',
        'motifRejet' => 'Documents non conformes.',
    ])->assertStatus(200)
      ->assertJsonPath('profil.statut', 'rejete');

    $this->assertDatabaseHas('profils', ['id' => $this->profil->id, 'statut' => 'rejete', 'motif_rejet' => 'Documents non conformes.']);
    $this->assertDatabaseHas('users',   ['id' => $this->membre->id, 'statut' => 'rejete', 'actif' => 0]);

    Mail::assertSent(\App\Mail\ProfilRejete::class, fn ($mail) => $mail->hasTo($this->membre->email));
});

// --- Suspension ---

it('suspend le profil — synchronise profil ET user', function () {
    Passport::actingAs($this->admin);

    Document::factory()->create(['profil_id' => $this->profil->id]);

    // D'abord valider
    $this->patchJson("/api/admin/profiles/{$this->profil->id}/status", ['statut' => 'valide']);

    // Puis suspendre
    $this->patchJson("/api/admin/profiles/{$this->profil->id}/status", [
        'statut'     => 'suspendu',
        'motifRejet' => 'Comportement inapproprié.',
    ])->assertStatus(200)
      ->assertJsonPath('profil.statut', 'suspendu');

    $this->assertDatabaseHas('profils', ['id' => $this->profil->id, 'statut' => 'suspendu']);
    $this->assertDatabaseHas('users',   ['id' => $this->membre->id, 'statut' => 'suspendu', 'actif' => 0]);

    Mail::assertSent(\App\Mail\ProfilRejete::class);
});

// --- Résilience mail ---

it('retourne 200 même si le mail échoue', function () {
    Mail::fake();
    Mail::shouldReceive('to->send')->andThrow(new \Exception('SMTP error'));

    Passport::actingAs($this->admin);
    Document::factory()->create(['profil_id' => $this->profil->id]);

    $this->patchJson("/api/admin/profiles/{$this->profil->id}/status", [
        'statut' => 'valide',
    ])->assertStatus(200);

    $this->assertDatabaseHas('profils', ['id' => $this->profil->id, 'statut' => 'valide']);
});
