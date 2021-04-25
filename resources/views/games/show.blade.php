@extends('layouts.app')
@section('title','Choose a game')
@section('content')



<h1>{{$game->name}}</h1>
<p>{{$game->description}}</p>



<p>
    download everything you need to develop your solution right here
    <a href = {{$file}}>Download</a>
</p>

@if (Auth::check())
    @if (Storage::disk('public')->exists('/'.$game->name.'/'.Auth::user()->id.'/player.py'))
        <p>You have an AI uploaded, would you like to upload a new one</p>
    @else
        <p>Upload your AI</p>
    @endif
@endif

<form method="POST" action={{route('gameStore')}} enctype="multipart/form-data">
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
    @if($uploadError)
        <p style='color:red'>Invalid upload</p>

    @endif
    <p>UploadFile: <input type="file" name="file" class="form-control"></p>

    <input type="submit" value="Submit">
    <input type="hidden" name="id" value="{{$game->id}}">
</form>

@foreach ($game->tournaments as $tourny)
<ul>
    <li>
        <a href="{{route('tournamentShow',['id'=>$tourny->id,])}}">
            {{$tourny->name}}
        </a>
    </li>
</ul>
@endforeach

@endsection
