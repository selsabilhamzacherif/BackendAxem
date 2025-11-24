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

            $table->date('date');
            $table->time('heure');
            $table->string('type'); // contrôle, partiel, final, etc.
            $table->string('niveau'); // ex : L1, L2, etc.

            // Clés étrangères
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('salle_id')->constrained('salles')->cascadeOnDelete();
            $table->foreignId('groupe_id')->constrained('groupes')->cascadeOnDelete();
            $table->foreignId('superviseur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();

            // Statut et champs optionnels
            $table->string('statut')->default('brouillon'); // brouillon, validé, publié
            $table->text('reclamation_chef')->nullable();
            $table->dateTime('date_reclamation')->nullable();
            $table->dateTime('date_publication')->nullable();

            $table->timestamps();
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
