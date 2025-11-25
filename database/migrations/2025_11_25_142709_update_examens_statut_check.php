<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer l'ancienne contrainte CHECK si elle existe
        DB::statement("
            ALTER TABLE examens
            DROP CONSTRAINT IF EXISTS examens_statut_check
        ");

        // Ajouter une nouvelle contrainte avec les statuts mis à jour
        DB::statement("
            ALTER TABLE examens
            ADD CONSTRAINT examens_statut_check
            CHECK (statut IN ('brouillon', 'validé', 'publié', 'à_modifier'))
        ");
    }

    public function down(): void
    {
        // Remettre la contrainte d'origine (si besoin)
        DB::statement("
            ALTER TABLE examens
            DROP CONSTRAINT IF EXISTS examens_statut_check
        ");

        DB::statement("
            ALTER TABLE examens
            ADD CONSTRAINT examens_statut_check
            CHECK (statut IN ('brouillon', 'validé', 'publié'))
        ");
    }
};

