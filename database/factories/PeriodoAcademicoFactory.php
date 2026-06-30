<?php

namespace Database\Factories;

use App\Models\OfertaAcademica;
use App\Models\PeriodoAcademico;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodoAcademico>
 */
class PeriodoAcademicoFactory extends Factory
{
    protected $model = PeriodoAcademico::class;

    public function definition(): array
    {
        $fechaInicio = fake()->dateTimeBetween('-6 months', '+3 months');
        $fechaFin = (clone $fechaInicio)->modify('+'.fake()->numberBetween(1, 6).' months');

        $fechaInicioInscripcion = (clone $fechaInicio)->modify('-1 month');
        $fechaFinInscripcion = (clone $fechaInicio)->modify('-1 day');

        $fechaInicioRetiro = (clone $fechaInicio)->modify('+1 week');
        $fechaFinRetiro = (clone $fechaInicio)->modify('+3 weeks');

        $fechaInicioCierre = (clone $fechaFin)->modify('-2 weeks');
        $fechaFinCierre = (clone $fechaFin);

        return [
            'oferta_academica_id' => OfertaAcademica::factory(),
            'nombre' => fake()->unique()->sentence(3),
            'tipo' => fake()->randomElement(['semestral', 'anual']),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'fecha_inicio_inscripcion' => $fechaInicioInscripcion,
            'fecha_fin_inscripcion' => $fechaFinInscripcion,
            'fecha_inicio_cierre' => $fechaInicioCierre,
            'fecha_fin_cierre' => $fechaFinCierre,
            'fecha_inicio_retiro' => $fechaInicioRetiro,
            'fecha_fin_retiro' => $fechaFinRetiro,
            'numero_maximo_materias' => fake()->numberBetween(4, 7),
            'estado' => fake()->randomElement(['inscripcion', 'cierre', 'retiro', 'terminado']),
        ];
    }
}
