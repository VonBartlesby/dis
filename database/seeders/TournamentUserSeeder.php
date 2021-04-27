<?php

namespace Database\Seeders;

use App\Models\TournamentUser;
use Illuminate\Database\Seeder;

class TournamentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for($i = 1;$i<21;$i++){
            TournamentUser::create([
                'user_id' =>$i,
                'tournament_id' =>1
            ]);
        }







        TournamentUser::create([
            'user_id' =>1,
            'tournament_id' =>2
        ]);
        TournamentUser::create([
            'user_id' =>2,
            'tournament_id' =>2
        ]);
    }
}
