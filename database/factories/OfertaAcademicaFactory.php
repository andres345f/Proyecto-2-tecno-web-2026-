<?php

namespace Database\Factories;

use App\Models\OfertaAcademica;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfertaAcademica>
 */
class OfertaAcademicaFactory extends Factory
{
    protected $model = OfertaAcademica::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(3, true),
            'codigo' => fake()->unique()->bothify('???-####'),
            'descripcion' => fake()->sentence(),
        ];
    }
}
