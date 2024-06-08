<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Http\Request;

class DeckController extends Controller
{
    public function index() {
        
        $decks = Deck::with('creator')->get();

        foreach ($decks as $deck) {
            $deck->creator_user_name = $deck->creator->name;
        }

        return response()->json($decks);
    }

    public function show($id) {

        $deck = Deck::findOrFail($id);

        $cards = $deck->cards;

        $deck->cards = $cards;

        return response()->json(['deck' => $deck]);
    }
}
