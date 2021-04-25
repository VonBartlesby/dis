@extends('layouts.app')
@section('title','Choose a game')
@section('content')
<h1>Browse User games</h1>

<ul>

    @foreach ($games as $game)
    <li>
        <p><a href = "{{route('gameShow',['id'=>$game->id])}}">{{$game->name}} </a></p>

    </li>
    @endforeach
</ul>

{!! $games->render() !!}
@endsection
