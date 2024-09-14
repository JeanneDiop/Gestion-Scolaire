<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class CreateParentasTable extends Migration
{
    public function up()
    {
        // Supprime la table si elle existe déjà
        Schema::dropIfExists('parentas');
        
        Schema::create('parentas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->string('profession'); // La colonne 'profession' est obligatoire
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parentas');
    }
}
