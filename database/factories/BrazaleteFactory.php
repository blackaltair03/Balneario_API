<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Balneario;
use App\Models\User;
use App\Models\Evento;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brazalete>
 */
class BrazaleteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'codigo_qr' => $this->faker->unique()->uuid,
            'status' => $this->faker->randomElement(['pendiente', 'activo', 'rechazado']),
            'evento_id' => Evento::factory(),
            'balneario_id' => Balneario::factory(),
            'checador_id' => function (array $attributes) {
                return User::factory()->create(['rol_id' => 3, 'balneario_id' => $attributes['balneario_id']])->id;
            },
            'fecha_verificacion' => $this->faker->dateTimeBetWeen('-1 month', 'now')
        ];
    }
}
