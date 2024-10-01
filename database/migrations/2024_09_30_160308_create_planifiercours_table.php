<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Classe; // Importation de la classe Classe
use App\Models\Cour;   // Importation de la classe Cour

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('planifier_cours', function (Blueprint $table) {
            $table->id();
            $table->date('date_cours');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->string('jour_semaine');
            $table->integer('duree');
            $table->enum('statut', ['prévu', 'annulé', 'reporté'])->default('prévu');
            $table->string('type_cours')->nullable();
            $table->integer('semestre')->nullable();
            $table->string('matiere')->nullable();

            // Clés étrangères en utilisant les classes
            $table->foreignIdFor(Classe::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Cour::class)->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planifier_cours');
    }
};
