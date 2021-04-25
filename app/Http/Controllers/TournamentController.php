<?php

namespace App\Http\Controllers;

use App\Jobs\RunTournament;
use App\Models\GameUser;
use App\Models\Tournament;
use App\Models\TournamentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use Symfony\Component\Process\Process;
use App\Models\Game;
use Illuminate\Support\Facades\Storage;

class TournamentController extends Controller
{

    public function index(){
        $tournys = Tournament::paginate(15);
        return view('tournaments.index',['tournaments' => $tournys]);
    }

    public function show($id){
        $tournament = Tournament::findOrFail($id);
        $joinable = date('Y-m-d H:i:s') < $tournament->deadline;
        $enteredNum = $tournament->users->count();
        $results = [];
        $file = Storage::url('tournament_'.$tournament->id.'.zip');
        if($tournament->is_complete){
            $results = TournamentUser::where('tournament_id','=',$id)->orderBy('score','desc')->get();
        }
        if(Auth::check()){
            $userId = Auth::user()->id;
            $gameId = $tournament->game->id;
            $enteredUser = TournamentUser::where('user_id','=',$userId)->where('tournament_id','=',$id)->get()->count()>0;
            $userValidGame = GameUser::where('user_id','=',$userId)->where('game_id','=',$gameId)->get()->count() > 0;
            return view('tournaments.show',['tournament' => $tournament,
                                            'enteredNum'=>$enteredNum,
                                            'enteredUser'=>$enteredUser,
                                            'userValidGame'=>$userValidGame,
                                            'results'=>$results,
                                            'joinable'=>$joinable,
                                            'file'=>$file]);
        }


        return view('tournaments.show',['tournament' => $tournament,
                                        'enteredNum'=>$enteredNum,
                                        'enteredUser'=>false,
                                        'userValidGame'=>false,
                                        'results'=>$results,
                                        'joinable'=>$joinable,
                                        'file'=>$file]);
    }

    public function enter($id){

        $tournament = Tournament::findOrFail($id);
        if(date('Y-m-d H:i:s') > $tournament->deadline){
            return date('Y-m-d H:i:s').$tournament->deadline;
        }
        $userId = Auth::user()->id;
        if(TournamentUser::where('user_id','=',$userId)->where('tournament_id','=',$id)->get()->count()>0){
            return $this->show($id);
        }
        $gameId = $tournament->game->id;
        if(GameUser::where('user_id','=',$userId)->where('game_id','=',$gameId)->get()->count()>0){
            $pivot = new TournamentUser;
            $pivot->user_id = $userId;
            $pivot->tournament_id = $id;
            $pivot->save();
        }

        return $this->show($id);
    }

    public function leave($id){
        $tournament = Tournament::findOrFail($id);
        if(date('Y-m-d H:i:s') > $tournament->deadline){
            return date('Y-m-D H:i:s');
        }
        $userId = Auth::user()->id;
        $entered = TournamentUser::where('user_id','=',$userId)->where('tournament_id','=',$id)->first();
        $entered->delete();
        return $this->show($id);
    }

    public function run($id){
        $tournament = Tournament::findOrFail($id);
        $game = $tournament->game;
        if(!Auth::check()){
            return "you must be logged in to run a game";
        }
        if(Auth::user()->id != $tournament->user_id){
            return "you are not the owner of this game";
        }
        if($tournament->users->count() < 2){
            return "you need more players";
        }

        $test = '';
        $enteredUsers = $tournament->users;
        foreach($enteredUsers as $user){
            if($this->validateUser($tournament,$user)){
                $gamer = TournamentUser::where('user_id','=',$user->id)->where('tournament_id','=',$id)->first();
                $gamer->valid = true;
                $gamer->save();
            }

        }
        $validUsers = TournamentUser::where('tournament_id','=',$id)->where('valid','=',true)->get();

        foreach($validUsers as $i){
            foreach($validUsers as $j){
                if(!($i->id == $j->id)){
                    $process = new Process(['python',Storage::path('public') . '/'.$game->name.'/'.$game->name.'.py',$i->id,$j->id]);
                    try{

                        $process->mustRun();
                        $output = $process->getOutput();
                        if($output == 1){
                            $winner = TournamentUser::where('user_id','=',$i->id)->where('tournament_id','=',$id)->first();
                            $winner->score = $winner->score + 3;
                            $winner->save();
                        }else if($output == -1){
                            $winner = TournamentUser::where('user_id','=',$j->id)->where('tournament_id','=',$id)->first();
                            $winner->score = $winner->score + 3;
                            $winner->save();
                        }else if($output == 0){
                            $player1 = TournamentUser::where('user_id','=',$i->id)->where('tournament_id','=',$id)->first();
                            $player2 = TournamentUser::where('user_id','=',$j->id)->where('tournament_id','=',$id)->first();
                            $player1->score = $player1->score + 1;
                            $player2->score = $player2->score + 1;
                            $player1->save();
                            $player2->save();
                        }
                    }catch (Exception $exception){
                        dd($exception);
                    }
                }
            }
        }
        $tournament->is_complete = true;
        $tournament->save();
        return $this->show($id);


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
                if($fill == -1){
                    $wins++;
                }
            }catch (Exception $exception){
                $fill = $exception->getMessage();
                dd($exception);
            }
        }
        return $wins == $gamesNum;
    }

    public function test($id){
        $tournament = Tournament::findOrFail($id);
        if(!Auth::check()){
            throw(new Exception(Auth::user()));
        }
        if(Auth::user()->id != $tournament->user_id){
            throw(new Exception( "you are not the owner of this game"));
        }
        if($tournament->users->count() < 2){
            throw(new Exception("you need more players"));
        }
        $job = new RunTournament($id);
        $job->dispatch($id);
        return redirect()->route('tournamentShow', ['id' => 1]);
    }

    public function create(){
        $games = Game::get();
        return view('tournaments.create',['games' => $games]);
    }

    public function store(Request $request){
        if(Auth::check()){
            $validateData = $request->validate([
                'title' => 'required|max:255',
                'game' => 'required',
                'deadline' => 'required|date|after:date',
                'time' => 'required',
                'maxUsers' => 'required|'
            ]);
            $deadline = $request->deadline . ' ' . $request->time . ':00';
            $tournament = new Tournament();
            $tournament->name = $request->title;
            if($request->private != null){
                $tournament->is_private = true;
            }
            $tournament->game_id = $request->game;
            $tournament->max_users = $request->maxUsers;
            $tournament->deadline = $deadline;
            $tournament->user_id = Auth::user()->id;
            $tournament->save();
            return redirect()->route('tournamentIndex');
        }
        return 'you must be logged in';

    }
}
