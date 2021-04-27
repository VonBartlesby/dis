<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;
class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Game::create([
            'name' => 'Gomoku',
            'description' => 'Gomoku, also called Five in a Row,
             is an abstract strategy board game.
             https://en.wikipedia.org/wiki/Gomoku',

        ]);
        Game::create([
            'name' => 'TicTacToe',
            'description' => 'Tic Tac Toe or Noughts and crosses is a simple 2 player game,
            with the aim of getting 3 of your pices in a row.
            https://en.wikipedia.org/wiki/Tic-tac-toe',
        ]);
    }
}
