@extends('layouts.app')
@section('title','Choose a game')
@section('content')
<h1>Create a new tournament</h1>

<form method="POST" action={{route('tournamentStore')}} enctype="multipart/form-data">
    @csrf
    @if ($errors->any())
        <div>
            Errors:
            <ul>
                @foreach ($errors->all() as $error)
                   <li> {{$error}}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p>Title: <input type="text" name="title"
        value="{{old('name')}}"></p>

    <p>Private: <input type="checkbox" name="private"></p>
    <label for='game'>Choose the game this tournament is for</label>
    <select id='game' name='game'>
        @foreach ($games as $game)
            <option value = {{$game->id}}>{{$game->name}}</option>
        @endforeach
    </select>

    <p>Max Users: <input type="number" name="maxUsers"></p>

    <p>Deadline: <input type="date" name="deadline"> <input type="time" name="time"></p>


    <input type="submit" value="Submit">

    <a href="{{route('tournamentIndex')}}">Cancel</a>
</form>


@endsection
