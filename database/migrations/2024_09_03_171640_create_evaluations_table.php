<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Apprenant;
use App\Models\Cours;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('nom_evaluation');
            $table->string('niveau_education');
            $table->enum('categorie', ['theorique', 'pratique','sport'])->nullable();
            $table->enum('type_evaluation', ['devoir1','devoir2','examen'])->nullable();
            $table->date('date_evaluation');
            $table->foreignIdFor(Apprenant::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Cours::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
