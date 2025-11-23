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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('motDePasse');
            $table->string('role');           // étudiant, enseignant, chef_dept, responsable_plan
            $table->string('specialite')->nullable();
            $table->string('departement')->nullable();

            // Clé étrangère vers groupes (nullable)
            $table->unsignedBigInteger('groupe_id')->nullable();
            $table->foreign('groupe_id')
                  ->references('id')
                  ->on('groupes')
                  ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
