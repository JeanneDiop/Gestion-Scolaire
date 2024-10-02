<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Classe;
use App\Models\Enseignant;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enseignant_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Classe::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(Enseignant::class)->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enseigant_classes');
    }
};
