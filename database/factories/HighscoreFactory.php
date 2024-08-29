<?php

namespace Database\Factories;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Highscore>
 */
class HighscoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deck_id' => Deck::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'time' => $this->faker->randomFloat(2, 1, 10000),
        ];
    }
}
