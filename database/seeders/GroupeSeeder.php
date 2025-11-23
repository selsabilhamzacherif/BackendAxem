<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Groupe;

class GroupeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some specific groups first (avoid duplicates)
        Groupe::updateOrCreate(['nomGroupe' => 'G1'], ['niveau' => 'L1']);
        Groupe::updateOrCreate(['nomGroupe' => 'G2'], ['niveau' => 'L2']);

        // Then create additional groups via factory (unique noms)
        Groupe::factory()->count(8)->create();
    }
}
