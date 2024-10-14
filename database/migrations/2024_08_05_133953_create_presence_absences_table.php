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
        Schema::create('presence_absences', function (Blueprint $table) {
            $table->id();
            $table->enum('absent', ['oui', 'non'])->default('oui')->nullable();
            $table->enum('present', ['oui', 'non'])->default('oui')->nullable();
            $table->date('date_present')->nullable();
            $table->date('date_absent')->nullable();
            $table->string('raison_absence');
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
        Schema::dropIfExists('presence_absences');
    }
};
