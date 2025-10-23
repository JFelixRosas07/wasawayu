<?php

namespace Database\Factories;

use App\Models\DetalleRotacion;
use App\Models\PlanRotacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetalleRotacionFactory extends Factory
{
    protected $model = DetalleRotacion::class;

    public function definition()
    {
        return [
            'plan_id' => PlanRotacion::factory(),
            'anio' => $this->faker->numberBetween(2020, 2030),
            'cultivo_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}