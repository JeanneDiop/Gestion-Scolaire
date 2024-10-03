<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Apprenant;
use App\Models\Programme;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parcours', function (Blueprint $table) {
            $table->string('nom')->nullable();
            $table->string('description')->nullable();
            $table->integer('credits')->nullable();
            $table->date('date_creation')->nullable();
            $table->date('date_modification')->nullable();
            $table->foreignIdFor(Apprenant::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Programme::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcours');
    }
};
