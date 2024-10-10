<?php

use App\Models\Classe;
use App\Models\Tuteur;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apprenants', function (Blueprint $table) {
            $table->id();
            $table->date('date_naissance');
            $table->string('image')->nullable();
            $table->string('lieu_naissance');
            $table->string('numero_CNI')->unique()->nullable();
            $table->string('numero_carte_scolaire')->unique()->nullable();
            $table->string('niveau_education');
            $table->enum('statut_marital', ['marié', 'celibataire','divorcé','veuve','veuf'])->nullable();
            $table->foreignIdFor(Classe::class)->constrained()->nullable()->onDelete('cascade');
            $table->foreignIdFor(Tuteur::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apprenants');
    }
};
