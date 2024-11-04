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
            $table->string('langue_enseignee')->nullable();
            $table->string('importer_programme')->nullable();
            $table->string('exporter_programme')->nullable();
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
