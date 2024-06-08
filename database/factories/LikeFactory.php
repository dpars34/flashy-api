<?php

namespace Database\Factories;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Like>
 */
class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        static $userDeckCombinations = [];

        $userIds = User::pluck('id')->toArray();
        $deckIds = Deck::pluck('id')->toArray();

        do {
            $userId = $this->faker->randomElement($userIds);
            $deckId = $this->faker->randomElement($deckIds);
            $combination = $userId . '-' . $deckId;
        } while (in_array($combination, $userDeckCombinations));

        $userDeckCombinations[] = $combination;

        return [
            'user_id' => $userId,
            'deck_id' => $deckId,
        ];
    }
}
