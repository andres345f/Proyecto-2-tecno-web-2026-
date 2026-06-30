<?php

namespace Database\Factories;

use App\Models\OfertaAcademica;
use App\Models\PlanPago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanPago>
 */
class PlanPagoFactory extends Factory
{
    protected $model = PlanPago::class;

    public function definition(): array
    {
        return [
            'oferta_academica_id' => OfertaAcademica::factory(),
            'nombre' => fake()->unique()->sentence(3),
            'tipo' => fake()->randomElement(['unico', 'por_periodo']),
            'monto_matricula' => fake()->randomFloat(2, 10, 200),
            'monto_cuota' => fake()->randomFloat(2, 10, 150),
            'cantidad_cuotas' => fake()->numberBetween(1, 12),
        ];
    }
}
