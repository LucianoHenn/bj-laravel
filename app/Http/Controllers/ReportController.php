<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CasinoCard;
use App\Models\Play;
use App\Models\PlayerCard;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function resultAvg(){

       $result = DB::select("SELECT 100 * COUNT(*) / (select COUNT(*) from plays)  as y, result as name from plays  where result is not null group by result");

        return json_encode($result);
    }

    public function dailyGames(){

        $result = DB::select("SELECT COUNT(*) as y, date(created_at) as dia from plays group by   date(created_at)");

        return json_encode($result);
    }

    public function dealerWinningCards(){

        $result = DB::select("SELECT COUNT(*) as y, casinoTotal as x from plays where casinoTotal in (17,18,19,20,21) and result = 'LOST' group by   casinoTotal order by x");

        return json_encode($result);
    }

}
