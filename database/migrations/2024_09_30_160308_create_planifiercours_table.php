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
        Schema::create('planifiercours', function (Blueprint $table) {
            $table->id();
            $table->date('date_cours');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->string('jour_semaine');
            $table->integer('duree');
            $table->enum('statut', ['prévu', 'annulé','reporté'])->default('prévu');
            $table->string('type_cours')->nullable();
            $table->integer('semestre')->nullable();
            $table->string('matiere')->nullable();
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
        Schema::dropIfExists('planifiercours');
    }
};
