<?php

namespace Database\Factories;

use App\Models\MatriculaCarrera;
use App\Models\OfertaAcademica;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatriculaCarrera>
 */
class MatriculaCarreraFactory extends Factory
{
    protected $model = MatriculaCarrera::class;

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(['is_estudiante' => true]),
            'oferta_academica_id' => OfertaAcademica::factory(),
            'fecha_matricula' => fake()->dateTimeBetween('-1 year', 'now'),
            'estado' => 'activo',
        ];
    }
}
