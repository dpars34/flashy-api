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
        'categories',
        'left_option',
        'right_option',
        'count',
    ];

    public function cards()
    {
        return $this->hasMany(Card::class);
    }
}
