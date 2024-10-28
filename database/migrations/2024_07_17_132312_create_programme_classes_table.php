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
        Schema::create('programme_classes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('cycle');
            $table->string('niveau_education');
            $table->string('niveau_classe');
            $table->string('annee_scolaire');
            $table->string('langue_enseignee');
            $table->string('objectif_generaux')->nullable();
            $table->string('objectif_specifiques')->nullable();
            $table->string('bareme')->nullable;
            $table->enum('frequence_evaluation', ['Hebdomadaire', 'Mensuel', 'Trimestriel'])->nullable();
            $table->enum('type_evaluation', ['Formative', 'Sommative'])->nullable;
            $table->string('importer_programme')->nullable;
            $table->string('exporter_')->nullable;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programme_classes');
    }
};
