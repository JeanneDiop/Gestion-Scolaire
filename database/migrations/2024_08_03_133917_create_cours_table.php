<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Enseignant;
use App\Models\ProgrammeClasse;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

    Schema::create('cours', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('description')->nullable();
        $table->enum('niveau_education', ['maternelle', 'primaire', 'secondaire','superieur']);
        $table->string('niveau_classe');
        $table->string('heure_allouee');
        $table->string('duree_recommander_sceance');
        $table->string('categorie_cours');
        $table->string('bareme')->nullable();
        $table->string('type_exercice')->nullable();
        $table->enum('frequence_evaluation', ['Hebdomadaire', 'Mensuel', 'Trimestriel'])->nullable();
        $table->enum('type_evaluation', ['Formative', 'Sommative'])->nullable();
        $table->enum('etat', ['encours', 'terminé', 'annulé'])->default('encours');
        $table->integer('credits')->nullable();
        $table->integer('coefficient')->nullable();
        $table->integer('semestre')->nullable();
        $table->foreignIdFor(Enseignant::class)->nullable()->constrained()->onDelete('set null');
        $table->foreignIdFor(ProgrammeClasse::class)->nullable()->constrained()->onDelete('set null');
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cours');
    }
};
