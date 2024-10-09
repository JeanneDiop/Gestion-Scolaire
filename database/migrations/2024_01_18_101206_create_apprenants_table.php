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
            $table->foreignId('tuteur_id')->constrained()->nullable()->onDelete('set null');
            $table->foreignId('classe_id')->nullable()->constrained()->onDelete('cascade');
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
