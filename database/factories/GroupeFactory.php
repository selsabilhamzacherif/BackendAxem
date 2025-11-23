<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Groupe;

class GroupeFactory extends Factory
{
    protected $model = Groupe::class;

    public function definition()
    {
        $niveauOptions = ['L1', 'L2', 'L3', 'M1', 'M2'];

        // Use faker unique to avoid duplicate nomGroupe values
        $num = $this->faker->unique()->numberBetween(1, 9999);

        return [
            'nomGroupe' => 'G' . $num,
            'niveau' => $this->faker->randomElement($niveauOptions),
        ];
    }
}
