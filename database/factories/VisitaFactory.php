<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Visita;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visita>
 */
class VisitaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url' => '/page-'.$this->faker->unique()->numerify('####'),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'usuario_id' => null,
        ];
    }

    /**
     * Define a state for a visit from an authenticated user.
     */
    public function authenticated(): static
    {
        return $this->state(fn (array $attributes) => [
            'usuario_id' => User::factory(),
        ]);
    }
}
