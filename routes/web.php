<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();
Route::get('/', [App\Http\Controllers\GameController::class, 'index'])->name('gameIndex');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/runRand', [App\Http\Controllers\GameController::class, 'runRand'])->name('runRand');

Route::get('/game', [App\Http\Controllers\GameController::class, 'index'])->name('gameIndex');

Route::post('/game', [App\Http\Controllers\GameController::class, 'store'])->name('gameStore');

Route::get('/game/{id}', [App\Http\Controllers\GameController::class, 'show'])->name('gameShow');
Route::get('/game/val/{id}', [App\Http\Controllers\GameController::class, 'validateSolution'])->name('gameVal');

Route::get('/tournament', [App\Http\Controllers\TournamentController::class, 'index'])->name('tournamentIndex');
Route::get('/tournament/create', [App\Http\Controllers\TournamentController::class, 'create'])->name('tournamentCreate');
Route::post('/tournament/store', [App\Http\Controllers\TournamentController::class, 'store'])->name('tournamentStore');
Route::get('/tournament/{id}', [App\Http\Controllers\TournamentController::class, 'show'])->name('tournamentShow');
Route::delete('/tournament/{id}', [App\Http\Controllers\TournamentController::class, 'leave'])->name('tournamentLeave');
Route::get('/tournament/enter/{id}', [App\Http\Controllers\TournamentController::class, 'enter'])->name('tournamentEnter');
Route::get('/tournament/run/{id}', [App\Http\Controllers\TournamentController::class, 'test'])->name('tournamentRun');


