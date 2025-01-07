<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_vente')->nullable();
            $table->string('nom_vente')->nullable();
            $table->text('description')->nullable();
            $table->decimal('prix_vente', 10, 2)->nullable();
            $table->enum('unite', ['fcfa', 'euro', 'dollars', 'eur', 'usd'])->nullable();
            $table->integer('quantite')->nullable();
            $table->integer('quantite_disponible_stock')->nullable();
            $table->enum('type_vente', ['produit', 'service'])->nullable();
            $table->foreignId('compte_comptable_id')->nullable()->constrained('compte_comptables')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventes');
    }
};
