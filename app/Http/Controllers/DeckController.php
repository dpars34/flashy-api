<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Deck;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DeckController extends Controller
{
    public function index() {
        $decks = Deck::with(['cards', 'creator', 'highscores.user', 'likedUsers'])->get();
    
        $decks = $decks->map(function($deck) {
            return [
                'id' => $deck->id,
                'created_at' => $deck->created_at,
                'updated_at' => $deck->updated_at,
                'creator_user_id' => $deck->creator_user_id,
                'name' => $deck->name,
                'description' => $deck->description,
                'categories' => json_decode($deck->categories) ?: [],  // Ensure categories is an array
                'left_option' => $deck->left_option,
                'right_option' => $deck->right_option,
                'count' => $deck->count,
                'liked_users' => $deck->likedUsers->pluck('id')->toArray(),  // Return liked_users as an array
                'creator' => $deck->creator,
                'cards' => $deck->cards,
                'highscores' => $deck->highscores,
            ];
        });
    
        return response()->json($decks);
    }
    

    public function show($id) {

        $deck = Deck::with(['cards', 'creator', 'highscores.user'])->findOrFail($id);

        return response()->json([
            'id' => $deck->id,
            'name' => $deck->name,
            'description' => $deck->description,
            'categories' => $deck->categories,
            'left_option' => $deck->left_option,
            'right_option' => $deck->right_option,
            'count' => $deck->count,
            'cards' => $deck->cards,
            'creator' => $deck->creator,
            'highscores' => $deck->highscores,
            'liked_users' => $deck->likedUsers->pluck('id')->toArray(), // Return user IDs as an array
        ]);
    }

    public function store(Request $request) {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categories' => 'nullable|array',
            'left_option' => 'required|string|max:15',
            'right_option' => 'required|string|max:15',
            'count' => 'required|integer',
            'creator_user_id' => 'required|exists:users,id',
            'cards' => 'required|array',
            'cards.*.text' => 'required|string|max:255',
            'cards.*.note' => 'nullable|string',
            'cards.*.answer' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Create the deck
            $deck = Deck::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
                'categories' => json_encode($validated['categories']),
                'left_option' => $validated['left_option'],
                'right_option' => $validated['right_option'],
                'count' => $validated['count'],
                'creator_user_id' => $validated['creator_user_id'],
            ]);

            // Create the cards associated with the deck
            foreach ($validated['cards'] as $cardData) {
                Card::create([
                    'text' => $cardData['text'],
                    'note' => $cardData['note'] ?? '',
                    'answer' => $cardData['answer'],
                    'deck_id' => $deck->id,
                ]);
            }

            DB::commit();

            // Return the newly created deck with its cards
            return response()->json($deck->load('cards'), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e], 500);
        }
    }

    public function like($deckId) {

        $user = Auth::user();

        if ($user) {
            $like = Like::where('deck_id', $deckId)->where('user_id', $user->id)->first();

            if ($like) {
                $likedUsers = Like::where('deck_id', $deckId)->pluck('user_id');
                return response()->json(['message', 'Deck already liked', 'liked_users' => $likedUsers], 200);
            } 
    
            Like::create([
                'deck_id' => $deckId,
                'user_id' => $user->id,
            ]);

            $likedUsers = Like::where('deck_id', $deckId)->pluck('user_id');
            return response()->json(['message' => 'Deck liked', 'liked_users' => $likedUsers], 201);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function unlike($deckId) {

        $user = Auth::user();

        if ($user) {
            $like = Like::where('deck_id', $deckId)->where('user_id', $user->id)->first();
    
            if ($like) {
                $like->delete();
                $likedUsers = Like::where('deck_id', $deckId)->pluck('user_id');
                return response()->json(['message' => 'Deck unliked', 'liked_users' => $likedUsers], 200);
            } else {
                $likedUsers = Like::where('deck_id', $deckId)->pluck('user_id');
                return response()->json(['message' => 'Deck has not been liked', 'liked_users' => $likedUsers], 200);
            }
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
