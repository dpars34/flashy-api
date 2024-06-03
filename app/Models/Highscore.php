<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Highscore extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', // Remove this line if you are using auto-increment ID
        'deck_id',
        'user_id',
        'time',
    ];

    public function deck()
    {
        return $this->belongsTo(Deck::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
