<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Classe;
use App\Models\Cours;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->string('file_name')->nullable();
            $table->string('nom')->nullable();
            $table->string('niveau_education')->nullable();
            $table->string('niveau_classe')->nullable();
            $table->string('cycle')->nullable();
            $table->string('annee_scolaire')->nullable();
            $table->string('langue_enseignee')->nullable();
            $table->string('importer_programme')->nullable();
            $table->string('exporter_programme')->nullable();
            $table->string('matiere')->nullable();
            $table->string('categorie')->nullable();
            $table->text('competences_essentielles')->nullable();
            $table->string('leçons')->nullable();
            $table->string('type_exercices')->nullable();
            $table->string('volume_horaire')->nullable();
            $table->string('duree_seance')->nullable();
            $table->string('mode_evaluation')->nullable();
            $table->string('heure_debut')->nullable();
            $table->string('heure_fin')->nullable();
            $table->string('bareme')->nullable();
            $table->string('source')->default('manuel');
            $table->foreignIdFor(Classe::class)->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(Cours::class)->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
