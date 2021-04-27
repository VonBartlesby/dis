<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Nathan',
            'email' => 'nathan@email.com',
            'password' => Hash::make('helloworld'),
        ]);
        User::factory()->count(20)->create();
    }
}
