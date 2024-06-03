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
        $userIds = User::pluck('id')->toArray();

        if (empty($userIds)) {
            throw new \Exception('No existing users found to create decks.');
        }

        $creatorUserId = $this->faker->randomElement($userIds);

        $options = ['history', 'math', 'science', 'language'];
        $likedUsers = $this->faker->randomElements($userIds, rand(1, 5));

        return [
            'name' => $this->faker->word,
            'description' => $this->faker->text(20),
            'categories' => json_encode([$this->faker->randomElement($options)]),
            'left_option' => "false",
            'right_option' => "true",
            'creator_user_id' => $creatorUserId,
            'count' => 0,
            'liked_users' => json_encode($likedUsers),
        ];
    }
}
