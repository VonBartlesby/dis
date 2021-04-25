<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->boolean('is_private')->default(false);
            $table->boolean('is_complete')->default(false);
            $table->bigInteger('game_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->integer('max_users')->unsigned()->default(2);
            $table->dateTime('deadline');
            $table->foreign('game_id')->references(('id'))
            ->on('games')->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('user_id')->references(('id'))
            ->on('users')->onDelete('cascade')->onUpdate('cascade');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tournament_users');
        Schema::dropIfExists('tournaments');
    }
}
