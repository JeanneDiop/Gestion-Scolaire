<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Niveau;
use App\Models\Ecole;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('niveau_ecoles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Ecole::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Niveau::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveau_ecoles');
    }
};
