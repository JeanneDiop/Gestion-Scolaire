<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Enseignant;
use App\Models\Classe;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enseignant_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Enseignant::class)->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(Classe::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enseignant_classes');
    }
};
