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
        Schema::create('examens', function (Blueprint $table) {
            $table->id();

            $table->date('date');               // Date de l'examen
            $table->time('heure');              // Heure de l'examen
            $table->string('type');             // Type d'examen (ex: partiel, final)
            $table->string('niveau');
                   // Niveau (ex: L1, L2, M1...)

            // Clés étrangères
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('salle_id');
            $table->unsignedBigInteger('groupe_id');
            $table->unsignedBigInteger('superviseur_id')->nullable();

            $table->enum('statut', ['brouillon','validé','publié'])->default('brouillon');

            $table->timestamps();

            // Définir les relations
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            $table->foreign('salle_id')->references('id')->on('salles')->onDelete('cascade');
            $table->foreign('groupe_id')->references('id')->on('groupes')->onDelete('cascade');
            $table->foreign('superviseur_id')->references('id')->on('utilisateurs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examens');
    }
};
