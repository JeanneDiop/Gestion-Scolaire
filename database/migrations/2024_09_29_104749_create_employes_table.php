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
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->enum('genre', ['Femme', 'Homme']);
            $table->string('numero_CNI')->unique();
            $table->string('nationalité')->nullable();
            $table->string('image')->nullable();
            $table->string('numero_identification_employe')->unique();
            $table->string('poste_occupé');
            $table->date('date_debut_service');
            $table->enum('statut_employé', ['Permanent', 'Temporaire','Vacataire']);
            $table->enum('type_contrat', ['CDI', 'CDD','Contrat','Vacataire']);
            $table->string('horaires_travail')->nullable();
            $table->string('superviseur')->nullable();
            $table->string('salaire_base');
            $table->enum('type_salaire', ['Mensuel', 'Horaire']);
            $table->string('prime_indemnités')->nullable();
            $table->string('cotisation_sociales')->nullable();
            $table->string('part_employeur')->nullable();
            $table->string('retenue_salaire')->nullable();
            $table->enum('mode_paiement', ['Virement', 'Bancaire','Espèce','Chèque']);
            $table->string('banque_domiciliation')->nullable();
            $table->string('numero_compte_bancaire')->unique()->nullable();
            $table->string('cv_diplomes')->nullable();
            $table->string('certification_formations')->nullable();
            $table->string('contrat_travail')->nullable();
            $table->string('ancienneté')->nullable();
            $table->double('evaluation_performance')->nullable();
            $table->string('commentaires_notes')->nullable();
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
