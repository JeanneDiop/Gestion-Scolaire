<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('date_heure')->nullable();
            $table->enum('lieu', ['Salle', 'Exterieur', 'En ligne'])->nullable();
            $table->foreignId('salle_id')->nullable()->constrained('salles')->onDelete('set null'); 
            $table->string('lieu_exterieur')->nullable();
            $table->string('lien_evenement')->nullable();
            $table->enum('recurrence', ['Quotidien', 'Hebdomadaire', 'Mensuel','Annuel'])->nullable();
            $table->string('ressource')->nullable();
            $table->foreignIdFor(User::class, 'responsable_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('type_evenement')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};
