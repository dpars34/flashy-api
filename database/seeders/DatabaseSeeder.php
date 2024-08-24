<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Card;
use App\Models\Deck;
use App\Models\Like;
use App\Models\User;
use App\Models\Category;
use App\Models\Highscore;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        Category::factory(10)->create();

        Deck::factory()->count(5)->create()->each(function ($deck) {
            $cards = Card::factory()->count(10)->make();
            $deck->cards()->saveMany($cards);
        
            $deck->update(['count' => $deck->cards()->count()]);
        });

        Highscore::factory(20)->create();
        Like::factory(20)->create();
    }
}
