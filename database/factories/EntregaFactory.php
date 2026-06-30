<?php

namespace Database\Factories;

use App\Models\Entrega;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entrega>
 */
class EntregaFactory extends Factory
{
    protected $model = Entrega::class;

    public function definition(): array
    {
        return [
            'tarea_id' => Tarea::factory(),
            'usuario_id' => User::factory(['is_estudiante' => true]),
            'ruta_archivo' => null,
            'fecha_entrega' => now(),
            'nota' => null,
            'retroalimentacion' => null,
        ];
    }

    public function withFile(): static
    {
        return $this->state(fn () => [
            'ruta_archivo' => 'tareas/1/1_documento.pdf',
        ]);
    }

    public function graded(float $nota = 85.0): static
    {
        return $this->state(fn () => [
            'nota' => $nota,
            'retroalimentacion' => fake()->sentence(),
        ]);
    }
}
