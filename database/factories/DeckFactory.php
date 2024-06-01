<?php

namespace Database\Factories;

use Illuminate\Support\Arr;
use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deck>
 */
class DeckFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $options = ['history', 'math', 'science', 'language'];
        
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->text(20),
            'categories' => json_encode([$this->faker->randomElement($options)]),
            'left_option' => "false",
            'right_option' => "true",
            'creator_user_id' => User::inRandomOrder()->first()->id
            // count will be set when cards are created
        ];
    }
}
