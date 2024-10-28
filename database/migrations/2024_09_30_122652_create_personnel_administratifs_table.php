<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personnel_administratifs', function (Blueprint $table) {
            $table->id();
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->string('numero_identification_employe')->unique();
            $table->string('poste_occupé');
            $table->string('image')->nullable();
            $table->string('nationalité')->nullable();
            $table->date('date_debut_service')->nullable();
            $table->enum('departement_service', ['Administratif', 'Comptabilité','Maintenance']);
            $table->enum('statut_employé', ['Permanent', 'Temporaire','Vacataire']);
            $table->enum('type_contrat', ['CDI', 'CDD','Contrat','Vacataire']);
            $table->string('horaires_travail')->nullable();
            $table->string('superviseur')->nullable();
            $table->string('salaire_base');
            $table->string('numero_CNI')->unique();
            $table->enum('type_salaire', ['Mensuel', 'Horaire']);
            $table->string('prime_indemnités')->nullable();
            $table->string('cotisation_sociales')->nullable();
            $table->string('part_employeur')->nullable();
            $table->string('retenue_salaire')->nullable();
            $table->enum('mode_paiement', ['Virement', 'Bancaire','Espèce','Chèque']);
            $table->string('banque_domiciliation')->nullable();
            $table->string('numero_compte_bancaire')->nullable();
            $table->string('cv_diplomes')->nullable();
            $table->string('contrat_travail')->nullable();
            $table->string('ancienneté')->nullable();
            $table->double('evaluation_performance')->nullable();
            $table->string('commentaires_notes')->nullable();
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_administartifs');
    }
};
