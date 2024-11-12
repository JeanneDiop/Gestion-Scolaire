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
        Schema::create('categorie_cours', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->foreignId('cours_id')->constrained('cours')->onDelete('cascade');
            $table->string('volume_horaire')->nullable();
            $table->string('leçons')->nullable();
            $table->string('type_exercices')->nullable();
            $table->string('duree_seance')->nullable();
            $table->enum('frequence_evaluation', ['Hebdomadaire', 'Mensuel', 'Trimestriel'])->nullable();
            $table->enum('mode_evaluation', ['Formative', 'Sommative'])->nullable();
            $table->string('heure_debut')->nullable();
            $table->string('heure_fin')->nullable();
            $table->string('bareme')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorie_cours');
    }
};
