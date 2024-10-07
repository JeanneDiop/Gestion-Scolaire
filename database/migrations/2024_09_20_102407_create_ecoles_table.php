<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Directeur;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ecoles', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('adresse');
            $table->string('telephone')->unique();
            $table->string('email')->unique();
            $table->string('siteweb')->unique()->nullable();
            $table->string('logo')->unique()->nullable();
            $table->integer('annee_creation')->nullable();
            $table->string('type_ecole');
            $table->string('niveau_education');
            $table->foreignIdFor(Directeur::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecoles');
    }
};
