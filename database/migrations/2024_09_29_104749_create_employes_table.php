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
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('telephone')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('adresse');
            $table->string('poste');
            $table->string('image')->nullable();
            $table->date('date_embauche');
            $table->enum('statut', ['permanent', 'vacataire','contractuel','honoraire']);
            $table->enum('type_salaire', ['fixe', 'horaire']);
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->enum('genre', ['Femme', 'Homme']);
            $table->enum('statut_marital', ['marié', 'celibataire','divorcé','veuve','veuf']);
            $table->string('numero_CNI')->unique();
            $table->string('numero_securite_social')->unique()->nullable();
            $table->date('date_fin_contrat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};
