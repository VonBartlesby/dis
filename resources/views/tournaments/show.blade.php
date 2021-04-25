@extends('layouts.app')
@section('title','Choose a game')
@section('content')

<h1>{{$tournament->name}}</h1>
<p>Currently entered: {{$enteredNum}}</p>
<p>Deadline: {{$tournament->deadline}}
@if (Auth::check())
    @if($joinable)
        @if($enteredUser)
            <p>You have already entered. Leave?</p>
            <form method="POST" action={{route('tournamentLeave',['id'=>$tournament->id])}}>
                @csrf
                @method('DELETE')
                <input type="submit" value="Leave">
            </form>
        @elseif ($userValidGame)
            <p>You have a valid game.</p>
            <a href="{{route('tournamentEnter',['id'=>$tournament->id])}}">Enter</a>

        @else
            <p>You need to upload a solution for this game before you enter</p>
            <a href = "{{route('gameShow',['id'=>$tournament->game_id])}}">Game</a>
        @endif

    @else
        <p>Too late</p>

    @endif
@else
    <p>You must be logged in to enter</p>

@endif

@if ($tournament->is_complete)
    <p>
        download Resuts
        <a href = {{$file}}>Download</a>
    </p>


    @foreach ($results as $res)
        <p> {{App\Models\User::findOrFail($res->user_id)->name}} with {{$res->score}} points. Beat Rand? {{$res->valid}}</p>
    @endforeach
@else
    <a href="{{route('tournamentRun',['id'=>$tournament->game_id])}}">Run</a>
@endif






@endsection
