<?php

namespace Database\Factories;

use App\Models\MatriculaCarrera;
use App\Models\MatriculaPeriodo;
use App\Models\PeriodoAcademico;
use App\Models\PlanPago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatriculaPeriodo>
 */
class MatriculaPeriodoFactory extends Factory
{
    protected $model = MatriculaPeriodo::class;

    public function definition(): array
    {
        return [
            'matricula_carrera_id' => MatriculaCarrera::factory(),
            'periodo_academico_id' => PeriodoAcademico::factory(),
            'plan_pago_id' => PlanPago::factory(),
            'fecha_matricula' => fake()->dateTimeBetween('-1 year', 'now'),
            'estado' => 'activo',
        ];
    }
}
