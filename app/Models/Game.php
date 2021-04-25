<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;
    //relationship of games made using this model
    public function tournaments(){
        return $this->hasMany('App\Models\Tournament');
    }

    public function users(){
        return $this->BelongsToMany('App\Models\User','game_users');
    }
}
