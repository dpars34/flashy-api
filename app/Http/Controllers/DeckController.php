<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use Illuminate\Http\Request;

class DeckController extends Controller
{
    public function index() {
        
        $decks = Deck::all();
        return response()->json($decks);
    }

    public function show($id) {

        $deck = Deck::findOrFail($id);

        $cards = $deck->cards;

        $deck->cards = $cards;

        return response()->json(['deck' => $deck]);
    }
}
