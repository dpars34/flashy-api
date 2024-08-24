<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeckController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// TEST
Route::get('/test', function () {
    return [
        'hello' => true
    ];
});

// DECKS
Route::get('/decks', [DeckController::class, 'index']);
Route::get('/decks/{id}', [DeckController::class, 'show']);

// LOGIN
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// REGISTER
Route::post('/register-confirmation', [AuthController::class, 'registerConfirmation']);
Route::post('/register', [AuthController::class, 'register']);

// CATEGORIES
Route::get('/categories', [CategoryController::class, 'index']);

// PROTECTED ROUTES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'details']);
    Route::post('/user-edit-confirmation', [AuthController::class, 'editConfirmation']);
    Route::post('/user-edit', [AuthController::class, 'edit']);

    Route::post('/validate-password', [AuthController::class, 'validateOldPassword']);
    Route::post('/update-password', [AuthController::class, 'changePassword']);
    Route::post('/check-password', [AuthController::class, 'checkPassword']);
    Route::post('/delete-account', [AuthController::class, 'deleteAccount']);

    Route::post('/submit-deck', [DeckController::class, 'store']);

    Route::post('/like-deck/{deckId}', [DeckController::class, 'like']);
    Route::delete('/like-deck/{deckId}', [DeckController::class, 'unlike']);
});