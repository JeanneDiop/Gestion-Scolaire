<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Apprenant;
use App\Models\Enseignant;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->string('nom_rapport')->nullable();
            $table->enum('type_utilisateur', ['apprenant', 'enseignant'])->default('apprenant');
            $table->string('commentaire_apprenant')->nullable();
            $table->string('commentaire_enseignant')->nullable();
            $table->date('date_commentaire')->nullable();
            $table->foreignIdFor(Apprenant::class)->nullable()->constrained('apprenants')->onDelete('cascade');
            $table->foreignIdFor(Enseignant::class)->nullable()->constrained('enseignants')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
