<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_user_id',
        'text',
        'note',
        'left_option',
        'right_option',
        'answer',
        'deck_id',
    ];

    public function deck()
    {
        return $this->belongsTo(Deck::class);
    }
}
