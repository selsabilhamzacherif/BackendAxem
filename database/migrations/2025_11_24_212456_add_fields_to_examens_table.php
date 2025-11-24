<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examens', function (Blueprint $table) {
            $table->text('reclamation_chef')->nullable();
            $table->dateTime('date_reclamation')->nullable();
            $table->dateTime('date_publication')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('examens', function (Blueprint $table) {
            $table->dropColumn(['reclamation_chef', 'date_reclamation', 'date_publication']);
        });
    }
};
