<?php

namespace Database\Seeders;

use App\Models\Tournament;
use DateTime;
use Illuminate\Database\Seeder;

class TournamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $date = "13-04-2022 18:38:00";
        $format = "d-m-Y H:i:s";
        Tournament::create([
            'name' => 'Ai class coursework tournmy',
            'is_private' => false,
            'max_users' => 30,
            'game_id' =>1,
            'user_id' =>1,
            'deadline' => DateTime::createFromFormat($format,$date),
        ]);
        Tournament::create([
            'name' => 'Tic Tac Toe',
            'is_private' => false,
            'max_users' => 2,
            'game_id' =>2,
            'user_id' =>1,
            'deadline' => DateTime::createFromFormat($format,$date),
        ]);
    }
}
