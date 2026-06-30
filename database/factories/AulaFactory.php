<?php

namespace Database\Factories;

use App\Models\Aula;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aula>
 */
class AulaFactory extends Factory
{
    protected $model = Aula::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->randomElement(['Aula 101', 'Aula 102', 'Laboratorio A', 'Laboratorio B', 'Aula Magna', 'Salón de Conferencias']),
            'codigo' => fake()->unique()->bothify('???-###'),
            'capacidad' => fake()->numberBetween(10, 60),
        ];
    }
}
