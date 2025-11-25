<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE contraintes ALTER COLUMN "date" DROP NOT NULL');
            DB::statement('ALTER TABLE contraintes ALTER COLUMN "heure" DROP NOT NULL');
        } else {
            // MySQL / MariaDB
            DB::statement('ALTER TABLE contraintes MODIFY `date` DATE NULL');
            DB::statement('ALTER TABLE contraintes MODIFY `heure` TIME NULL');
        }
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE contraintes ALTER COLUMN "date" SET NOT NULL');
            DB::statement('ALTER TABLE contraintes ALTER COLUMN "heure" SET NOT NULL');
        } else {
            DB::statement('ALTER TABLE contraintes MODIFY `date` DATE NOT NULL');
            DB::statement('ALTER TABLE contraintes MODIFY `heure` TIME NOT NULL');
        }
    }
};
