<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\Highscore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HighscoreController extends Controller
{
    public function index() {

    }
    

    public function show($id) {

    }

    public function store(Request $request) {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'deck_id' => 'required|exists:decks,id',
            'user_id' => 'required|exists:users,id',
            'time' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $highscore = Highscore::create([
            'deck_id' => $request->deck_id,
            'user_id' => $request->user_id,
            'time' => $request->time,
        ]);

        $deck = Deck::with(['cards', 'creator', 'highscores.user', 'category'])->findOrFail($request->deck_id);

        return response()->json([
            'id' => $deck->id,
            'created_at' => $deck->created_at,
            'updated_at' => $deck->updated_at,
            'creator_user_id' => $deck->creator_user_id,
            'name' => $deck->name,
            'description' => $deck->description,
            'left_option' => $deck->left_option,
            'right_option' => $deck->right_option,
            'count' => $deck->count,
            'liked_users' => $deck->likedUsers->pluck('id')->toArray(),  // Return liked_users as an array
            'creator' => $deck->creator,
            'cards' => $deck->cards,
            'highscores' => $deck->highscores->sortBy('time')->take(3)->values(),
            'category' => $deck->category ? ['id' => $deck->category->id, 'name' => $deck->category->name] : null,
        ], 201);
    }
}
