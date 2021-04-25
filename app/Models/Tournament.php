<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;
    //the game that this tournament is using
    public function game(){
        return $this->belongsTo('App\Models\Game');
    }

    //many to many relationship between users and tournamesnts
    public function users(){
        return $this->BelongsToMany('App\Models\User','tournament_users');
    }

    public function user(){
        return $this->BelongsTo('App\Models\User');
    }
}
