<?php

use App\Models\Classe;
use App\Models\Tuteur;
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
        Schema::create('apprenants', function (Blueprint $table) {
            $table->id();
            $table->date('date_naissance');
            $table->string('image')->nullable();
            $table->string('lieu_naissance');
            $table->string('numero_CNI')->nullable()->unique();
            $table->string('numero_identification_eleve')->unique();
            $table->string('niveau_education');
            $table->string('nationalité')->nullable();
            $table->enum('regime_paiement', ['trimestriel', 'semestriel', 'annuel'])->nullable();
            $table->string('reduction_bourse')->nullable();
            $table->enum('statut_paiement_actuel', ['à jour', 'retard'])->nullable()->default('à jour');
            $table->string('references_factures')->nullable();
            $table->string('conditions_medicales')->nullable();
            $table->string('contact_urgence')->nullable();
            $table->string('note_resultat_anterieur')->nullable();
            $table->string('evaluations_specifiques')->nullable();
            $table->enum('langue_parlee_maison', ['Français', 'Anglais', 'Wolof', 'Sérère', 'Diola'])->nullable();
            $table->string('activités_extrascolaires')->nullable();
            $table->string('remarque_eleve')->nullable();
            $table->string('acte_naissance')->nullable();
            $table->string('autorisation_parentale')->nullable();
            $table->foreignIdFor(Classe::class)->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(Tuteur::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apprenants');
    }
};
