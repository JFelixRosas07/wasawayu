<?php

namespace Database\Factories;

use App\Models\Parcela;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParcelaFactory extends Factory
{
    protected $model = Parcela::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->word,
            'extension' => $this->faker->randomFloat(2, 1, 100),
            'ubicacion' => $this->faker->word,
            'tipoSuelo' => $this->faker->randomElement(['Arenoso', 'Arcilloso', 'Franco']),
            'usoSuelo' => $this->faker->randomElement(['Agricola', 'Ganadero', 'Mixto']),
            'poligono' => json_encode([
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-65.7, -17.58],
                        [-65.7, -17.59],
                        [-65.69, -17.59],
                        [-65.7, -17.58]
                    ]]
                ]
            ]),
            'agricultor_id' => User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}