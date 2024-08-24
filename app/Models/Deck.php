<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deck extends Model
{
    use HasFactory;
    protected $fillable = [
        'creator_user_id',
        'name',
        'description',
        'category_id',
        'left_option',
        'right_option',
        'count',
    ];

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function highscores() 
    {
        return $this->hasMany(Highscore::class)->orderBy('time')->limit(3);
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'deck_id');
    }

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'likes', 'deck_id', 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
