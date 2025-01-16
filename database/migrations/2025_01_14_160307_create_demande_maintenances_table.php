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
        Schema::create('demande_maintenances', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->enum('status', ['en_attente', 'en_cours', 'termine'])->nullable()->default('en_attente');
            $table->enum('niveau_priorite', ['faible', 'moyen', 'eleve'])->nullable()->default('moyen');
            $table->date('date_demande')->nullable();
            $table->string('emplacement')->nullable();
            $table->date('date_resolution')->nullable();
            $table->text('commentaire')->nullable();
            $table->foreignId('demandeur_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('personnel_id')->nullable()->constrained('personnel_administratifs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_maintenances');
    }
};
