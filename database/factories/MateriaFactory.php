<?php

namespace Database\Factories;

use App\Models\Materia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Materia>
 */
class MateriaFactory extends Factory
{
    protected $model = Materia::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->randomElement([
                'Física Aplicada',
                'Matemáticas Básicas',
                'Cálculo I',
                'Cálculo II',
                'Álgebra Lineal',
                'Programación I',
                'Programación II',
                'Base de Datos',
                'Redes de Computadoras',
                'Sistemas Operativos',
            ]),
            'codigo' => fake()->unique()->bothify('??-###'),
            'descripcion' => fake()->sentence(),
        ];
    }
}
