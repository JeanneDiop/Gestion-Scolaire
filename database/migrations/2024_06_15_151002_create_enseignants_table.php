<?php
use App\Models\User;
use App\Models\Classe;
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
        Schema::create('enseignants', function (Blueprint $table) {
            $table->id();
            $table->string('matiere_enseignée');
            $table->string('numero_identification_enseignant')->unique();
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->string('nationalité')->nullable();
            $table->string('image')->nullable();
            $table->string('numero_CNI')->unique();
            $table->string('niveau_enseignant');
            $table->enum('statut_enseignant', ['Permanent', 'Vacataire','Temporaire']);
            $table->date('date_debut_service');
            $table->enum('type_contrat', ['CDI', 'CDD','Contrat','Vacataire']);
            $table->string('heure_travail_hebdomadaire');
            $table->string('salaire_base');
            $table->enum('type_salaire', ['Mensuel', 'Horaire']);
            $table->string('prime_indemnités')->nullable();
            $table->string('cotisation_sociales')->nullable();
            $table->string('part_employeur')->nullable();
            $table->string('retenue_salaire')->nullable();
            $table->enum('mode_paiement', ['Virement', 'Bancaire','Espèce','Chèque']);
            $table->string('banque_domiciliation')->nullable();
            $table->string('numero_RIB')->nullable();
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
        Schema::dropIfExists('enseignants');
    }

};
