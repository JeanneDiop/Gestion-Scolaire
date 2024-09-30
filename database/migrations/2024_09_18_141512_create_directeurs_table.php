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
        Schema::create('directeurs', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->integer('annee_experience');
            $table->integer('date_prise_fonction');
            $table->string('numero_CNI')->unique();
            $table->string('qualification_academique');
            $table->enum('statut_marital', ['marié', 'celibataire','divorcé','veuve','veuf']);
            $table->date('date_embauche');
            $table->date('date_fin_contrat')->nullable();
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directeurs');
    }
};
