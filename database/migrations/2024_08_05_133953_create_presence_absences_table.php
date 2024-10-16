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
            $table->time('heure_arrivee')->nullable();
            $table->time('duree_retard')->nullable();
            $table->string('raison_absence')->nullable();
            $table->foreignIdFor(Apprenant::class)->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(Cours::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Enseignant::class)->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
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
