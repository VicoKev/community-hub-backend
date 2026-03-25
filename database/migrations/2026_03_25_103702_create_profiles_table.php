<?php

use App\Enums\ProfileStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
 
            $table->string('phone', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('bio')->nullable();
 
            $table->string('category', 50);
 
            $table->string('sector', 100)->nullable();
            $table->string('profession', 150)->nullable();
            $table->string('company_name', 150)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('commune', 100)->nullable();
            $table->string('address', 255)->nullable();
 
            $table->string('education_level', 50)->nullable();
            $table->string('institution', 150)->nullable();
            $table->string('field_of_study', 150)->nullable();
            $table->year('graduation_year')->nullable();
 
            $table->json('skills')->nullable();
            $table->json('languages')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->json('experiences')->nullable();
 
            $table->string('website')->nullable();
            $table->string('linkedin')->nullable();
            $table->boolean('show_email')->default(false);
            $table->boolean('show_phone')->default(false);
 
            $table->string('status', 20)->default(ProfileStatus::PENDING->value)->index();
            $table->foreignUuid('validated_by')->nullable()
                  ->references('id')->on('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->boolean('newsletter_subscribed')->default(false);
 
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['category', 'status']);
            $table->index(['sector', 'status']);
            $table->index(['city', 'status']);
            $table->index(['education_level', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
