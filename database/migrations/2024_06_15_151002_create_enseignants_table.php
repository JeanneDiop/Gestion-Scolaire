<?php
use App\Models\User;
use App\Models\Classe;
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
        Schema::create('enseignants', function (Blueprint $table) {

            $table->id();
            $table->string('specialite');
            $table->enum('statut_marital', ['marié', 'celibataire','divorcé','veuve','veuf']);
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->string('image')->nullable();
            $table->string('numero_CNI')->unique();
            $table->string('numero_securite_social')->unique();
            $table->string('niveau_ecole');
            $table->enum('statut', ['permanent', 'vacataire','contractuel','honoraire']);
            $table->date('date_embauche');
            $table->date('date_fin_contrat');
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enseignants');
    }

};
