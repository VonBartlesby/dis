<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;
use App\Models\Game;
use Illuminate\Support\Facades\Storage;
use App\Models\TournamentUser;
use Exception;
use Illuminate\Support\Facades\Auth;


use App\Models\Tournament;
use ZipArchive;

class RunTournament implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($in)
    {
        $this->id = $in;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tournament = Tournament::findOrFail($this->id);
        $game = $tournament->game;
        $enteredUsers = $tournament->users;
        foreach($enteredUsers as $user){
            if($this->validateUser($tournament,$user)){
                $gamer = TournamentUser::where('user_id','=',$user->id)->where('tournament_id','=',$this->id)->first();
                $gamer->valid = true;
                $gamer->save();
            }

        }
        $validUsers = TournamentUser::where('tournament_id','=',$this->id)->where('valid','=',true)->get();
        $folder = Storage::path('public') . '/'.$game->name.'/' .$tournament->name;
        $zip = new ZipArchive();
        $pathMine = Storage::path('public').'/tournament_'.$tournament->id.'.zip';
        //echo($pathMine);
        $ref = $zip->open($pathMine,ZipArchive::CREATE | ZipArchive::OVERWRITE);


        foreach($validUsers as $i){
            foreach($validUsers as $j){
                if(!($i->id == $j->id)){
                    $process = new Process(['python',Storage::path('public') . '/'.$game->name.'/'.$game->name.'.py',$i->id,$j->id]);
                    try{
                        $process->mustRun();
                        $outputFull = $process->getOutput();

                        //Storage::disk('public')->put('Gomoku/tournament'.$tournament->name.'/'. $i.'_vs_'.$j.'.txt', $outputFull);
                        //Storage::disk('public')->put('Gomoku/Results/file.txt', $fill);
                        $fileName = $i->user_id.'_vs_'.$j->user_id.'.txt';
                        echo($fileName);
                        //echo($fileDir);
                        //echo('/n');
                        //Storage::disk('public')->put($fileDir, $outputFull);
                        if($ref){
                            $zip->addFromString($fileName,$outputFull);
                        }else{
                            echo('failed to create zip');
                        }


                        $len = strlen($outputFull);

                        $output = $outputFull[$len-3];

                        if($output == ' '){
                            $winner = TournamentUser::where('user_id','=',$i->id)->where('tournament_id','=',$this->id)->first();
                            $winner->score = $winner->score + 3;
                            $winner->save();
                        }else if($output == '-'){
                            $winner = TournamentUser::where('user_id','=',$j->id)->where('tournament_id','=',$this->id)->first();
                            $winner->score = $winner->score + 3;
                            $winner->save();
                        }else if($output == 0){
                            $player1 = TournamentUser::where('user_id','=',$i->id)->where('tournament_id','=',$this->id)->first();
                            $player2 = TournamentUser::where('user_id','=',$j->id)->where('tournament_id','=',$this->id)->first();
                            $player1->score = $player1->score + 1;
                            $player2->score = $player2->score + 1;
                            $player1->save();
                            $player2->save();
                        }
                    }catch (Exception $exception){
                        throw($exception);
                    }
                }
            }
        }

        $tournament->is_complete = true;
        $tournament->save();
    }


    private function validateUser($tournament,$user){
        $gamesNum = 3;
        $wins = 0;
        $userId = $user->id;
        $game = Game::findOrFail($tournament->game_id);

        try{
            $files = Storage::path('public') . '/'.$game->name.'/'.$game->name.'.py';
            $file = Storage::files('Gomoku');
            $read = fopen($files,'r+');
        }catch(Exception $error){
            dd($error);
        }

        $process = new Process(['python',Storage::path('public') . '/'.$game->name.'/'.$game->name.'.py','GomokuAgentRand',$userId]);


        for($i = 0;$i<$gamesNum;$i++){
            try{
                $process->mustRun();
                $fill = $process->getOutput();
                $len = strlen($fill);
                $output = $fill[$len-3];
                if($output == '-'){
                    $wins++;
                }
            }catch (Exception $exception){
                $output = $exception->getMessage();
                dd($exception);
            }
        }
        return $wins == $gamesNum;
    }

}