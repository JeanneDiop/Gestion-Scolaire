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
        Schema::create('bulletin_notes', function (Blueprint $table) {
            $table->id();
            $table->json('disciplines')->nullable();
            $table->float('note_devoir', 5, 2)->nullable();
            $table->float('note_composition', 5, 2)->nullable();
            $table->float('moyenne_note', 5, 2)->nullable();
            $table->unsignedTinyInteger('coefficient')->nullable();
            $table->float('moyenne_x', 5, 2)->nullable();
            $table->unsignedTinyInteger('th')->nullable();
            $table->unsignedTinyInteger('rang_note')->nullable();
            $table->text('appreciation')->nullable();
            $table->json('total')->nullable();
            $table->float('moyenne_eleve', 5, 2)->nullable();
            $table->unsignedTinyInteger('rang_eleve')->nullable();
            $table->unsignedTinyInteger('total_retards')->nullable()->default(0);
            $table->unsignedTinyInteger('total_absences')->nullable()->default(0);
            $table->enum('observations', [
                'satisfaisant',
                'doit continuer',
                'peut mieux faire',
                'insuffisant',
                'risque redoubler',
                'risque exclusion',
                'felicitation',
                'encouragement',
                'tableau honneur',
                'avertissement',
                'blâme',
            ])->nullable();
            $table->string('semestre')->nullable();
            $table->text('observation_conseil_professeur')->nullable(); // Observation du conseil des professeurs
            $table->string('chef_etablissement')->nullable();
             $table->foreignId('apprenant_id')->nullable()->constrained('apprenants')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletin_notes');
    }
};
