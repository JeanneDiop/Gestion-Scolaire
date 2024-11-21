<?php

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
    Schema::create('historiques', function (Blueprint $table) {
    $table->id();
    $table->string('action');
    $table->string('message')->nullable();
    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('employe_id')->nullable()->constrained('employes')->onDelete('set null');
    $table->foreignId('programme_id')->nullable()->constrained('programmes')->onDelete('set null');
    $table->foreignId('classe_id')->nullable()->constrained('classes')->onDelete('set null');
    $table->foreignId('evaluation_id')->nullable()->constrained('evaluations')->onDelete('set null');
    $table->foreignId('salle_id')->nullable()->constrained('salles')->onDelete('set null');
    $table->foreignId('presence_id')->nullable()->constrained('presences')->onDelete('set null');
    $table->foreignId('note_id')->nullable()->constrained('notes')->onDelete('set null');
    $table->foreignId('cours_id')->nullable()->constrained('cours')->onDelete('set null');
    $table->foreignId('evenement_id')->nullable()->constrained('evenements')->onDelete('set null');
    $table->foreignId('categorie_cours_id')->nullable()->constrained('categorie_cours')->onDelete('set null');
    $table->foreignId('competence_id')->nullable()->constrained('competences')->onDelete('set null');
    $table->timestamps();

    // Index pour améliorer les performances de recherche
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historiques');
    }
};
