<?php

namespace Database\Factories;

use App\Models\Grupo;
use App\Models\Tarea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tarea>
 */
class TareaFactory extends Factory
{
    protected $model = Tarea::class;

    public function definition(): array
    {
        return [
            'grupo_periodo_id' => \App\Models\GrupoPeriodo::factory(),
            'titulo' => fake()->sentence(4),
            'descripcion' => fake()->paragraph(),
            'fecha_vencimiento' => fake()->dateTimeBetween('+1 week', '+2 months'),
            'puntaje_maximo' => fake()->randomFloat(2, 10, 100),
        ];
    }
}
