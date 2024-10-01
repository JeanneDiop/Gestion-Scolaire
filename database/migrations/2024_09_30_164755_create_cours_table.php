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
        Schema::create('cours', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('description')->nullable();
            $table->string('niveau_education');
            $table->string('matiere')->nullable();
            $table->string('type_cours')->nullable();
            $table->time('duree');
            $table->enum('etat', ['encours', 'terminé', 'annulé'])->default('encours');
            $table->integer('credits')->nullable();
            $table->foreignIdFor(Enseignant::class)->constrained()->onDelete('cascade');
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
