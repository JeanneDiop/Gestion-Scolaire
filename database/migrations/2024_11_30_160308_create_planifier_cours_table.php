<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Cours;
use App\Models\Classe;

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
            $table->time('duree')->nullable();
            $table->enum('statut', ['prévu', 'annulé', 'reporté'])->default('prévu');
            $table->string('annee_scolaire');
            $table->integer('semestre');
            $table->foreignIdFor(Cours::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Classe::class)->constrained()->onDelete('cascade');
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
