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
        Schema::create('personnel_administratifs', function (Blueprint $table) {
            $table->id();
            $table->string('poste');
            $table->string('image')->nullable();
            $table->enum('statut_emploie', ['permanent', 'vacataire','contractuel','honoraire']);
            $table->enum('type_salaire', ['fixe', 'horaire']);
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->enum('statut_marital', ['marié', 'celibataire','divorcé','veuve','veuf']);
            $table->string('numero_CNI')->unique();
            $table->string('numero_securite_social')->unique()->nullable();
            $table->date('date_embauche');
            $table->date('date_fin_contrat');
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_administartifs');
    }
};
