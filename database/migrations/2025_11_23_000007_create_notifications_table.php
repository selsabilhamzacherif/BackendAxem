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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('destinataire_id'); // FK vers utilisateurs
            $table->text('message');                        // Contenu de la notification
            $table->dateTime('date');                       // Date de création
            $table->enum('type', ['info','alerte','modification','validation']); // Type de notification
            $table->boolean('lu')->default(false);         // Lu ou non
            $table->json('metadata')->nullable();          // Données supplémentaires en JSON

            $table->timestamps();

            // Clé étrangère vers utilisateurs
            $table->foreign('destinataire_id')
                  ->references('id')
                  ->on('utilisateurs')
                  ->onDelete('cascade'); // Supprime les notifications si l'utilisateur est supprimé
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
