@extends('layouts.app')
@section('title','Choose a game')
@section('content')
<h1>Browse User games</h1>

<ul>

    @foreach ($tournaments as $tournament)
    <li>
        <p><a href = "{{route('tournamentShow',['id'=>$tournament->id])}}">{{$tournament->name}} </a></p>

    </li>
    @endforeach
</ul>

{!! $tournaments->render() !!}
@endsection
