<?php

namespace Database\Seeders;

use App\Models\GameUser;
use Illuminate\Database\Seeder;

class GameUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GameUser::create([
            'user_id' =>1,
            'game_id' =>1
        ]);
        GameUser::create([
            'user_id' =>2,
            'game_id' =>1
        ]);
        GameUser::create([
            'user_id' =>3,
            'game_id' =>1
        ]);
        GameUser::create([
            'user_id' =>4,
            'game_id' =>1
        ]);
    }
}
