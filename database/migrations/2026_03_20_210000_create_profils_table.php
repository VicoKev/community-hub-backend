<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profils', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignUuid('categorie_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->text('bio')->nullable();
            $table->string('localisation')->nullable();
            $table->string('quartier')->nullable();
            $table->string('arrondissement')->nullable();
            $table->string('site_web')->nullable();
            $table->json('reseaux_sociaux')->nullable();
            $table->enum('niveau_etude', ['Bac', 'Licence', 'Master', 'Doctorat', 'Autre'])->nullable();
            $table->string('secteur_activite')->nullable();
            $table->string('metier')->nullable();
            $table->json('competences')->nullable();
            $table->enum('visibilite_contact', ['PUBLIC', 'PRIVE'])->default('PRIVE');
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->text('motif_rejet')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profils');
    }
};
