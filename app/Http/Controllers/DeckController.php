<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Deck;
use App\Models\Like;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DeckController extends Controller
{
    public function index() {
        $decks = Deck::with(['cards', 'creator', 'highscores.user'])->get();
    
        $decks = $decks->map(function($deck) {
            return [
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
            ];
        });
    
        return response()->json($decks);
    }
    

    public function show($id) {

        $deck = Deck::with(['cards', 'creator', 'highscores.user',])->findOrFail($id);

        return response()->json([
            'id' => $deck->id,
            'name' => $deck->name,
            'description' => $deck->description,
            'left_option' => $deck->left_option,
            'right_option' => $deck->right_option,
            'count' => $deck->count,
            'cards' => $deck->cards,
            'creator' => $deck->creator,
            'highscores' => $deck->highscores,
            'liked_users' => $deck->likedUsers->pluck('id')->toArray(), // Return user IDs as an array
            'category' => $deck->category ? ['id' => $deck->category->id, 'name' => $deck->category->name] : null,
        ]);
    }

    public function store(Request $request) {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
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
                'category_id' => $validated['category_id'],
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
            return response()->json(['error' => $e->getMessage()], 500);
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

    public function getRandomDecks()
    {
        $categories = Category::inRandomOrder()->take(5)->get();

        $result = [];

        foreach ($categories as $category) {
            $decks = Deck::with(['cards', 'creator', 'highscores.user',])->where('category_id', $category->id)
                    ->leftJoin('likes', 'decks.id', '=', 'likes.deck_id') // Join with the likes table
                    ->select('decks.*', DB::raw('COUNT(likes.id) as likes_count')) // Count the likes
                    ->with('category') // Eager load the category relationship
                    ->groupBy('decks.id') // Group by deck ID
                    ->orderBy('likes_count', 'desc') // Order by the count of likes in descending order
                    ->take(3)
                    ->get();

            $structuredDecks = $decks->map(function($deck) use ($category) {
                return [
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
                    'category' => ['id' => $category->id, 'name' => $category->name, 'emoji' => $category->emoji],
                ];
            });
    
            $result[] = [
                'category' => ['id' => $category->id, 'name' => $category->name, 'emoji' => $category->emoji],
                'decks' => $structuredDecks 
            ];
        }
        return response()->json($result);
    }

    public function getDecksByCategory($id, Request $request)
    {

        $decks = Deck::with(['cards', 'creator', 'highscores.user', 'likedUsers'])
        ->where('category_id', $id)
        ->leftJoin('likes', 'decks.id', '=', 'likes.deck_id') // Join with the likes table
        ->select('decks.*', DB::raw('COUNT(likes.id) as likes_count')) // Count the likes
        ->groupBy('decks.id') // Group by deck ID
        ->orderBy('likes_count', 'desc') // Order by the count of likes in descending order
        ->paginate($request->input('limit', 10)); // Paginate results with a default limit of 10 per page


        $structuredDecks = $decks->map(function($deck) {
            return [
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
                'category' => [
                    'id' => $deck->category->id, 
                    'name' => $deck->category->name, 
                    'emoji' => $deck->category->emoji
                ],
            ];
        });

        return response()->json([
            'category' => [
                'id' => optional($decks->first()->category)->id, 
                'name' => optional($decks->first()->category)->name, 
                'emoji' => optional($decks->first()->category)->emoji,
            ],
            'decks' => $structuredDecks,
            'pagination' => [
                'current_page' => $decks->currentPage(),
                'last_page' => $decks->lastPage(),
                'per_page' => $decks->perPage(),
                'total' => $decks->total(),
            ],
        ]);
    }
    public function getLikedDecks(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Fetch the decks liked by the user
        $likedDecks = Deck::with(['cards', 'creator', 'highscores.user', 'likedUsers'])
            ->join('likes', 'decks.id', '=', 'likes.deck_id') // Join with the likes table
            ->where('likes.user_id', $user->id) // Filter for the authenticated user's likes
            ->select('decks.*', 'likes.created_at as liked_at', DB::raw('COUNT(likes.id) as likes_count')) // Include the liked timestamp
            ->groupBy('decks.id', 'likes.created_at') // Group by deck ID and liked timestamp
            ->orderBy('liked_at', 'desc') // Order by the time the deck was liked, descending
            ->paginate($request->input('limit', 10)); // Paginate results with a default limit of 10 per page

        // Map and structure the data like in your existing method
        $structuredLikedDecks = $likedDecks->map(function ($deck) {
            return [
                'id' => $deck->id,
                'created_at' => $deck->created_at,
                'updated_at' => $deck->updated_at,
                'creator_user_id' => $deck->creator_user_id,
                'name' => $deck->name,
                'description' => $deck->description,
                'left_option' => $deck->left_option,
                'right_option' => $deck->right_option,
                'count' => $deck->count,
                'liked_users' => $deck->likedUsers->pluck('id')->toArray(), // Return liked_users as an array
                'creator' => $deck->creator,
                'cards' => $deck->cards,
                'highscores' => $deck->highscores->sortBy('time')->take(3)->values(),
                'category' => [
                    'id' => optional($deck->category)->id,
                    'name' => optional($deck->category)->name,
                    'emoji' => optional($deck->category)->emoji,
                ],
            ];
        });

        // Return the response in a similar structure
        return response()->json([
            'decks' => $structuredLikedDecks,
            'pagination' => [
                'current_page' => $likedDecks->currentPage(),
                'last_page' => $likedDecks->lastPage(),
                'per_page' => $likedDecks->perPage(),
                'total' => $likedDecks->total(),
            ],
        ]);
    }

    public function getCreatedDecks(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Fetch the decks created by the user
        $createdDecks = Deck::with(['cards', 'creator', 'highscores.user', 'likedUsers'])
            ->where('creator_user_id', $user->id) // Filter decks created by the user
            ->select('decks.*', DB::raw('COUNT(likes.id) as likes_count')) // Count the likes for each deck
            ->leftJoin('likes', 'decks.id', '=', 'likes.deck_id') // Join with the likes table to get like count
            ->groupBy('decks.id') // Group by deck ID
            ->orderBy('created_at', 'desc') // Order by deck creation date, descending
            ->paginate($request->input('limit', 10)); // Paginate results with a default limit of 10 per page

        // Map and structure the data like in your existing method
        $structuredCreatedDecks = $createdDecks->map(function ($deck) {
            return [
                'id' => $deck->id,
                'created_at' => $deck->created_at,
                'updated_at' => $deck->updated_at,
                'creator_user_id' => $deck->creator_user_id,
                'name' => $deck->name,
                'description' => $deck->description,
                'left_option' => $deck->left_option,
                'right_option' => $deck->right_option,
                'count' => $deck->count,
                'liked_users' => $deck->likedUsers->pluck('id')->toArray(), // Return liked_users as an array
                'creator' => $deck->creator,
                'cards' => $deck->cards,
                'highscores' => $deck->highscores->sortBy('time')->take(3)->values(),
                'category' => [
                    'id' => optional($deck->category)->id,
                    'name' => optional($deck->category)->name,
                    'emoji' => optional($deck->category)->emoji,
                ],
            ];
        });

        // Return the response in a similar structure
        return response()->json([
            'decks' => $structuredCreatedDecks,
            'pagination' => [
                'current_page' => $createdDecks->currentPage(),
                'last_page' => $createdDecks->lastPage(),
                'per_page' => $createdDecks->perPage(),
                'total' => $createdDecks->total(),
            ],
        ]);
    }
    public function deleteDeck($id, Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Find the deck by ID and ensure it belongs to the authenticated user
        $deck = Deck::where('id', $id)->where('creator_user_id', $user->id)->first();

        // Check if the deck exists and belongs to the user
        if (!$deck) {
            return response()->json([
                'status' => 'error',
                'message' => 'Deck not found or you are not authorized to delete this deck.'
            ], 404);
        }

        // Delete any relationships (likes, cards, etc.)
        $deck->cards()->delete();
        $deck->likedUsers()->detach(); // Detach all likes

        // Finally, delete the deck
        $deck->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Deck deleted successfully.'
        ], 200);
    }
}
