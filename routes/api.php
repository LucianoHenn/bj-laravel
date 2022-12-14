<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// GAME ROUTES
Route::get('cards', [GameController::class, 'getAllCards']);
Route::get('init', [GameController::class, 'init']);
Route::get('resume', [GameController::class, 'resumeGame']);
Route::post('play', [GameController::class, 'startGame']);
Route::get('hit', [GameController::class, 'hit']);
Route::post('stay', [GameController::class, 'stay']);


// REPORTS ROUTES
Route::get('resultAvg', [ReportController::class, 'resultAvg']);
Route::get('dailyGames', [ReportController::class, 'dailyGames']);
Route::get('dealerWinningCards', [ReportController::class, 'dealerWinningCards']);

Route::get('unauthorized', function (){
    return response()->json(['error' => 'Unauthenticated.'], 401);
})->name('unauthorized');

Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('user-profile', [AuthController::class, 'userProfile']);
});
