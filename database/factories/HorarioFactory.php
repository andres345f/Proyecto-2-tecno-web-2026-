<?php

namespace Database\Factories;

use App\Models\Aula;
use App\Models\Horario;
use App\Models\GrupoPeriodo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Horario>
 */
class HorarioFactory extends Factory
{
    protected $model = Horario::class;

    public function definition(): array
    {
        $horaInicio = fake()->time('H:i', '14:00');
        $horaFin = fake()->time('H:i', '18:00');

        // Ensure hora_fin is after hora_inicio
        if ($horaFin <= $horaInicio) {
            $horaFin = '18:00';
        }

        return [
            'dia' => fake()->randomElement(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']),
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'aula_id' => Aula::factory(),
            'grupo_periodo_id' => GrupoPeriodo::factory(),
        ];
    }
}
