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

        $path = Storage::path('docker') . '/' . $game->name;
        foreach($validUsers as $i){
            foreach($validUsers as $j){
                if(!($i->id == $j->id)){
                    if(Storage::exists('docker/'.$game->name.'/player1.py')){
                        Storage::delete('docker/'.$game->name.'/player1.py');
                    }
                    if(Storage::exists('docker/'.$game->name.'/player2.py')){
                        Storage::delete('docker/'.$game->name.'/player2.py');
                    }
                    echo $j->user_id . "\n";
                    echo $i->user_id . "\n";
                    Storage::copy('docker/'.$game->name.'/'.$j->user_id.'.py', 'docker/'.$game->name.'/player1.py');
                    Storage::copy('docker/'.$game->name.'/'.$i->user_id.'.py', 'docker/'.$game->name.'/player2.py');

                    //$process = new Process(['python',Storage::path('public') . '/'.$game->name.'/'.$game->name.'.py',$i->id,$j->id]);
                    $dockerUp = new Process(['docker','build','--tag','current',$path]);
                    $dockerUp->mustRun();
                    $dockerRun = new Process(['docker','run','current']);
                    try{
                        $dockerRun->mustRun();
                        $outputFull = $dockerRun->getOutput();

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
        echo 'validating : ' . $userId . "\n";
        $game = Game::findOrFail($tournament->game_id);

        //$process = new Process(['python',Storage::path('public') . '/'.$game->name.'/'.$game->name.'.py','GomokuAgentRand',$userId]);

        if(Storage::exists('docker/'.$game->name.'/player1.py')){
            Storage::delete('docker/'.$game->name.'/player1.py');
        }
        if(Storage::exists('docker/'.$game->name.'/player2.py')){
            Storage::delete('docker/'.$game->name.'/player2.py');
        }

        Storage::copy('docker/'.$game->name.'/rand.py', 'docker/'.$game->name.'/player1.py');
        Storage::copy('docker/'.$game->name.'/'.$userId.'.py', 'docker/'.$game->name.'/player2.py');

        $path = Storage::path('docker') . '/' . $game->name;

        $dockerUp = new Process(['docker','build','--tag','current',$path]);
        $dockerUp->mustRun();
        $dockerRun = new Process(['docker','run','current']);




        for($i = 0;$i<$gamesNum;$i++){
            echo 'game: ' . $i;
            try{
                $dockerRun->mustRun();
                $output = $dockerRun->getOutput();
                $len = strlen($output);
                $output = $output[$len-3];
                if($output == '-'){
                    echo "     won\n";
                    $wins++;
                }else{
                    echo "     lost\n";
                }
            }catch (Exception $exception){
                $output = $exception->getMessage();
                dd($exception);
            }
        }
        echo "will delete\n";
        $dockerDown =  new Process(['docker','rmi','current','--force']);
        $dockerDown->mustRun();
        echo "has deleted\n";
        return $wins == $gamesNum;
    }

}
