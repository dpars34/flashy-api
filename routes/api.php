<?php

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

// PROTECTED ROUTES
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

});