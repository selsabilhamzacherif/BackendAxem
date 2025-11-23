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
        Schema::create('contraintes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('enseignant_id'); // FK vers utilisateurs (enseignants)
            $table->date('date');                         // Date de la contrainte
            $table->time('heure');                        // Heure de la contrainte
            $table->string('motif');                      // Motif de la contrainte

            $table->timestamps();

            // Définir la clé étrangère
            $table->foreign('enseignant_id')
                  ->references('id')
                  ->on('utilisateurs')
                  ->onDelete('cascade'); // Supprime la contrainte si l'enseignant est supprimé
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contraintes');
    }
};
