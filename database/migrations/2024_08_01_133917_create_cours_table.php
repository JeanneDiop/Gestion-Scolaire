<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Enseignant;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

    Schema::create('cours', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('description')->nullable();
        $table->string('niveau_education');
        $table->time('duree');
        $table->enum('etat', ['encours', 'terminé', 'annulé'])->default('encours');
        $table->integer('credits')->nullable();
        $table->foreignIdFor(Enseignant::class)->nullable()->constrained()->onDelete('set null');
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cours');
    }
};
