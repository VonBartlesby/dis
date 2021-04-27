<?php

namespace App\Http\Controllers;

use Exception;
use Symfony\Component\Process\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\TournamentUser;
use ZipArchive;
class GameController extends Controller
{
    public function runRand(){
        $dockerUp = new Process(['docker','build','--tag','current','/home/vagrant/code/dis/app/Http/Controllers/docker/go-docker']);
        $dockerUp->mustRun();
        $dockerRun = new Process(['docker','run','current']);
        $dockerRun->mustRun();
        $output = $dockerRun->getOutput();
        $dockerDown =  new Process(['docker','rmi','current','--force']);
        $dockerDown->mustRun();
        return $output;
        /*
        try{
            $files = Storage::path('public') . '/Gomoku/Gomoku.py';
            //dd($files);
            $file = Storage::files('Gomoku');

            //dd($file);
            $read = fopen($files,'r+');
        }catch(Exception $error){
            dd($error);
        }
        $process = new Process(['python',Storage::path('public') . '/Gomoku/gomoku.py','GomokuAgentRand','2']);
        //$process = new Process(['python',Storage::path('public') . '/Gomoku/gomoku.py','GomokuAgentRand','GomokuAgentRand']);
        //dd(Storage::url('app/public/Gomoku/gomoku.py'));
        try{
            $process->mustRun();
            $fill = $process->getOutput();
            dd($fill);
        }catch (Exception $exception){
            $fill = $exception->getMessage();
        }
        Storage::disk('public')->put('Gomoku/Results/file.txt', $fill);
        $len = strlen($fill);

        //$zip = new ZipArchive();
        //$zip->open(Storage::path('public').'/results.zip',ZipArchive::CREATE | ZipArchive::OVERWRITE);
        //$zip->addFromString('game.txt',$fill);
        //$zip->close();
        //dd($fill[$len-3]);
        return $fill;
        */
    }

    public function index(){
        if(!(empty($_GET))){
            return 'hacker!';
        }
        $games = Game::paginate(15);
        return view('games.index',['games' => $games]);
    }

    public function show($id){
        $game = Game::findOrFail($id);
        $errors = [];
        $file = Storage::url('/'.$game->name.'/'.$game->name.'.zip');
        return view('games.show',['game' => $game,'file' => $file,'uploadError'=>$errors]);
    }

    public function store(Request $request){
        $validateData = $request->validate([
            'file'=> 'required',
        ]);
        if(!Auth::check()){
            return 'you must be logged in to upload code';
        }
        //dd($request);

        $game = Game::findOrFail($request->id);

        $userId = Auth::user()->id;
        if($this->parseFile($request->file,$request->id)){
            $file = Storage::url('/'.$game->name.'/'.$game->name.'.zip');
            $errors = true;
            return view('games.show',['game' => $game,'file' => $file,'uploadError'=>$errors]);
        }
        $Ts = $game->tournaments;
        foreach($Ts as $t){
            if(date('Y-m-d H:i:s') > $t->deadline && TournamentUser::where('user_id','=',$userId)->where('tournament_id','=',$t->id)->get()->count()>0){
                return "poop";
            }
        }
        if(Storage::disk('public')->exists('/'.$game->name.'/'.$userId)){
            Storage::put('public/'.$game->name.'/'.$userId.'/player.py',file_get_contents($request->file));
        }else{
            $source = Storage::allFiles('public/'.$game->name.'/BasePlayer');
            Storage::put('public/'.$game->name.'/'.$userId.'/player.py',file_get_contents($request->file));

            foreach($source as $item){
                //dd($item);
                $split = explode('/',$item);
                $fileName = $split[count($split)-1];
                Storage::copy($item,'public/'.$game->name.'/'.$userId.'/'.$fileName);
                //Storage::put('public/'.$game->name.'/'.Auth::user()->id.'/player.py',$item);
            }
            $pivot = new GameUser;
            $pivot->game_id = $request->id;
            $pivot->user_id = $userId;
            $pivot->save();
        }

        return $this->show($game->id);
    }

    public function validateSolution($gameId){
        $wins = 0;
        $userId = Auth::user()->id;
        $game = Game::findOrFail($gameId);

        try{
            $files = Storage::path('public') . '/Gomoku/gomoku.py';
            $file = Storage::files('Gomoku');
            $read = fopen($files,'r+');
        }catch(Exception $error){
            dd($error);
        }
        $process = new Process(['python',Storage::path('public') . '/'.$game->name.'/'.$game->name.'.py','GomokuAgentRand',$userId]);
        //$process = new Process(['python',Storage::path('public') . '/'.$game->name.'/'.$game->name.'.py','GomokuAgentRand']);


        try{
            $process->mustRun();
            $fill = $process->getOutput();
            if($fill == -1){
                $wins++;
            }
        }catch (Exception $exception){
            $fill = $exception->getMessage();
        }
        return $fill;
    }

    private function parseFile($file,$gameID){
        $safe = true;
        $allowedImports = ['numpy','time','misc','legalMove','GomokuAgent'];
        $bannedTerms = ['file,open,print,breakpoint,compile,exec,staticmethod,__import__'];
        $content = explode("\n",$file->get());
        foreach($content as $line){
            if(str_contains($line,'import')){
                str_replace(";","#",$line);
                $split = explode("#",$line);
                foreach($split as $item){
                    $count = 0;
                    foreach($allowedImports as $module){
                        if(!(str_contains($item,$module))){
                            $count++;
                        }
                    }
                    if($count == count($allowedImports)){
                        return true;
                    }
                }
            }

            foreach($bannedTerms as $term){
                if(str_contains($line,$term)){
                    return true;
                }
            }
        }
    }

}

