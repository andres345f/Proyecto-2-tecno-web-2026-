<?php

namespace Database\Factories;

use App\Models\MallaCurricular;
use App\Models\Materia;
use App\Models\OfertaAcademica;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MallaCurricular>
 */
class MallaCurricularFactory extends Factory
{
    protected $model = MallaCurricular::class;

    public function definition(): array
    {
        return [
            'oferta_academica_id' => OfertaAcademica::factory(),
            'materia_id' => Materia::factory(),
            'semestre_orden' => fake()->numberBetween(1, 8),
        ];
    }
}
