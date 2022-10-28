<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerCard extends Model
{
    use HasFactory;

    protected $fillable = ['play_id', 'card_id', 'card_value'];
}
