<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeckCompletion extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'deck_id', 'completed_at'];

    // Define any relationships if necessary, like user and deck
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deck()
    {
        return $this->belongsTo(Deck::class);
    }
}
