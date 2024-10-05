<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClasseAssociationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classe_associations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('apprenant_id');
            $table->unsignedBigInteger('cours_id');
            $table->unsignedBigInteger('enseignant_id');
            $table->foreign('apprenant_id')->references('id')->on('apprenants')->onDelete('cascade');
            $table->foreign('cours_id')->references('id')->on('cours')->onDelete('cascade');
            $table->foreign('enseignant_id')->references('id')->on('enseignants')->onDelete('cascade');
            $table->unique(['apprenant_id', 'cours_id', 'enseignant_id']);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('classe_associations');
    }
}

