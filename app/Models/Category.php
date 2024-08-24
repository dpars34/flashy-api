<?php

namespace App\Models;

use App\Models\Deck;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function decks()
      {
          return $this->hasMany(Deck::class);
      }
}
