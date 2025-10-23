<?php

namespace Database\Factories;

use App\Models\PlanRotacion;
use App\Models\Parcela;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanRotacionFactory extends Factory
{
    protected $model = PlanRotacion::class;

    public function definition()
    {
        return [
            'parcela_id' => Parcela::factory(),
            'nombre' => 'P-' . $this->faker->unique()->numberBetween(1, 100),
            'anio_inicio' => $this->faker->numberBetween(2020, 2030),
            'creado_por' => User::factory(),
            'estado' => $this->faker->randomElement(['planificado', 'en_ejecucion', 'finalizado']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}