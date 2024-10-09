<?php

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
            $table->foreignId('tuteur_id')->constrained('tuteurs')->onDelete('set null')->nullable();
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade')->nullable();
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
