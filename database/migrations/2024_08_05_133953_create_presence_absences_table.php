<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Apprenant;
use App\Models\Cours;
use App\Models\Enseignant;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presence_absences', function (Blueprint $table) {
            $table->id();
            $table->enum('type_utilisateur', ['apprenant', 'enseignant'])->default('apprenant');
            $table->enum('statut', ['present', 'absent', 'retard'])->default('present');
            $table->date('date_present')->nullable();
            $table->date('date_absent')->nullable();
            $table->string('heure_arrivee')->nullable();
            $table->string('duree_retard')->nullable();
            $table->string('raison_absence')->nullable();
            $table->foreignIdFor(Apprenant::class)->nullable()->constrained('apprenants')->onDelete('cascade');
            $table->foreignIdFor(Cours::class)->constrained('cours')->onDelete('cascade');
            $table->foreignIdFor(Enseignant::class)->nullable()->constrained('enseignants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presence_absences');
    }
};
