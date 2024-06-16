<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Http\Request;

class DeckController extends Controller
{
    public function index() {
        
        $decks = Deck::with('creator')->get();

        return response()->json($decks);
    }

    public function show($id) {

        $deck = Deck::with(['cards', 'creator', 'highscores.user'])->findOrFail($id);

        return response()->json($deck);
    }
}
