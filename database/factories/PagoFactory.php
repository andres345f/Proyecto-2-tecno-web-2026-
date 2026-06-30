<?php

namespace Database\Factories;

use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pago>
 */
class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition(): array
    {
        return [
            'cuota_id' => Cuota::factory(),
            'monto_pagado' => fake()->randomFloat(2, 10, 200),
            'metodo_pago' => 'qr_pagofacil',
            'transaccion_id' => 'PF-'.strtoupper(uniqid()),
            'fecha_pago' => now(),
            'estado' => 'completado',
        ];
    }

    public function pendiente(): static
    {
        return $this->state(fn () => ['estado' => 'pendiente']);
    }

    public function completado(): static
    {
        return $this->state(fn () => ['estado' => 'completado']);
    }

    public function fallido(): static
    {
        return $this->state(fn () => ['estado' => 'fallido']);
    }
}
