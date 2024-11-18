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
        Schema::create('evenement_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evenement_id')->nullable()->constrained('evenements')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('classe_id')->nullable()->constrained('classes')->onDelete('cascade'); // Ajout de classe_id
            $table->timestamps();

            // Index pour une recherche rapide
            $table->unique(['evenement_id', 'user_id', 'classe_id']); // Mise à jour de l'index pour inclure classe_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evenement_users');
    }
};
