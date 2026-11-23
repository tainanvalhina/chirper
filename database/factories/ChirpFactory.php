<?php

namespace Database\Factories;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChirpFactory extends Factory
{
    protected $model = Chirp::class;

    public function definition(): array
    {
        return [
            // Cria um usuário relacionado ao Chirp.
            'user_id' => User::factory(),

            // Gera uma mensagem fictícia de até 255 caracteres.
            'message' => fake()->sentence(),
        ];
    }
}