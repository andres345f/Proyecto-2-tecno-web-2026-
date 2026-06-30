<?php

namespace Database\Factories;

use App\Models\Cuota;
use App\Models\MatriculaPeriodo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cuota>
 */
class CuotaFactory extends Factory
{
    protected $model = Cuota::class;

    public function definition(): array
    {
        return [
            'matricula_periodo_id' => MatriculaPeriodo::factory(),
            'descripcion' => fake()->sentence(3),
            'monto' => fake()->randomFloat(2, 10, 200),
            'fecha_vencimiento' => fake()->dateTimeBetween('now', '+1 year'),
            'estado' => 'pendiente',
        ];
    }
}
