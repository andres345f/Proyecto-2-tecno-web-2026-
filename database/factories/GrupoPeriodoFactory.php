<?php

namespace Database\Factories;

use App\Models\GrupoPeriodo;
use App\Models\Grupo;
use App\Models\PeriodoAcademico;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GrupoPeriodo>
 */
class GrupoPeriodoFactory extends Factory
{
    protected $model = GrupoPeriodo::class;

    public function definition(): array
    {
        return [
            'grupo_id' => Grupo::factory(),
            'periodo_academico_id' => PeriodoAcademico::factory(),
            'docente_id' => User::factory(['is_profesor' => true]),
            'cupo_maximo' => 35,
        ];
    }
}
