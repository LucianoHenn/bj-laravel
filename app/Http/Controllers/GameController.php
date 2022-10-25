<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Http\Controllers\Controller;
use App\Models\Play;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public  function getAllCards(Request $request){
        return response()->json([Card::all()]);
    }

    public  function  startGame(Request $request){
        $play = new Play;
        $play->
    }

}
