<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Http\Controllers\Controller;
use App\Models\CasinoCard;
use App\Models\Play;
use App\Models\PlayerCard;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public  function getAllCards(Request $request){

        return response()->json([Card::all()]);
    }

    public  function  startGame(Request $request){
        $user = auth('api')->user();
        if($play = Play::where('user_id', $user->id)->where('status', '!=', 'FINISHED')->first()){
            $play->status = 'FINISHED';
            $play->amountWonOrLost = $play->bet;
            $play->result = 'QUIT';
            $play->save();
        }
        $play = new Play;
        $play->user_id = $user->id;
        $play->status = 'NEW';
        $play->bet = $request->get('bet');
        $play->playerTotal = 0;
        $play->casinoTotal =0;
        $play->save();

        $this->dealCardToUser($play);
        $this->dealCardToDealer($play);
        $this->dealCardToUser($play);
        $this->dealCardToDealer($play);

        $userCards = DB::table('player_cards')->select('card_id','card_value')->where('play_id', $play->id)->orderBy('created_at')->get();
        $dealerCard = DB::table('casino_cards')->select('card_id','card_value')
            ->where('play_id', $play->id)->orderBy('created_at')->first();

        $this->calculateTotal($userCards, $play);

        $userTotal = $play->playerTotal;

        return response()->json(['userCards' =>  $userCards, 'dealerCard' => $dealerCard, 'userTotal' =>  $userTotal]);
    }

    public function hit(Request $request){
        $user = auth('api')->user();
        $play = Play::where('user_id', $user->id)->whereIn('status', ['PLAYING', 'NEW'])->latest('created_at')->first();
        if(!$play){
            return response()->json(['error' =>  'We didnt found your game, please start a new one']);
        }
        $this->dealCardToUser($play);

        $this->calculateTotal(true, $play);

        $userCards = DB::table('player_cards')->select('card_id','card_value')->where('play_id', $play->id)->orderBy('created_at')->get();

        $userTotal = $play->playerTotal;

        $play->status = 'PLAYING';

        if($userTotal > 21){
            $play->status = 'FINISHED';
            $play->result = 'LOST';
            $play->amountWonOrLost = $play->bet;
            $play->save();
            return response()->json(['userCards' =>  $userCards, 'userTotal' =>  $userTotal, 'status' =>   $play->status,
                                    'result' => $play->result, 'amountWonOrLost' => $play->amountWonOrLost]);
        }

        if ($userTotal === 21){
            $play->result = 'FINISHED';
        }


        $play->save();

        return response()->json(['userCards' =>  $userCards, 'userTotal' =>  $userTotal, 'status' =>   $play->status]);

    }

    public function stay(Request $request){
        $user = auth('api')->user();
        $play = Play::where('user_id', $user->id)->whereIn('status', ['PLAYING', 'NEW'])->latest('created_at')->first();
        if(!$play){
            return response()->json(['error' =>  'We didnt found your game, please start a new one']);
        }
        $this->dealersTurn($play);
        $play->amountWonOrLost = $play->bet;
        if($play->playerTotal > $play->casinoTotal || $play->casinoTotal > 21) {
            $play->result = 'WON';
        }elseif ($play->playerTotal === $play->casinoTotal){
            $play->result = 'TIE';
            $play->amountWonOrLost = 0;
        }else{
            $play->result = 'LOST';
        }
        $play->status = 'FINISHED';
        $play->save();
        $casinoCards = DB::table('casino_cards')->select('card_id','card_value')->where('play_id', $play->id)->orderBy('created_at')->get();
        return response()->json(['result' =>  $play->result, 'amountWonOrLost' => $play->amountWonOrLost,
            'casinoCards' => $casinoCards, 'casinoTotal' => $play->casinoTotal]);
    }

    public function dealersTurn($play){
        $this->calculateTotal(false, $play);
            while($play->casinoTotal <= 16){
                $this->dealCardToDealer($play);
                $this->calculateTotal(false, $play);
            }
    }


    public function dealCardToUser($play){

        $card = $this->getCard($play);

        $userCards = new PlayerCard;
        $userCards->play_id  = $play->id;
        $userCards->card_id = $card['id'];
        if($card['number'] >= 10){
            $userCards->card_value = 10;
        }elseif ($card['number'] === 1){
            $userCards->card_value = 11;
        }else
            $userCards->card_value = $card['number'];
        $userCards->save();
    }

    public function dealCardToDealer($play){
        $card = $this->getCard($play);

        $dealearCards = new CasinoCard;
        $dealearCards->play_id  = $play->id;
        $dealearCards->card_id = $card['id'];
        if($card['number'] >= 10){
            $dealearCards->card_value = 10;
        }elseif ($card['number'] === 1){
            $dealearCards->card_value = 11;
        }else
            $dealearCards->card_value = $card['number'];
        $dealearCards->save();
    }

    public function getCard($play){

        $cardsIds = [];
        $first = DB::table('player_cards')->select('card_id')->where('play_id', $play->id);
        $cardsAlreadyInUse = DB::table('casino_cards')->select('card_id')->where('play_id', $play->id)->union($first)->get()->toArray();
        foreach ($cardsAlreadyInUse as $cardUsed){
            array_push($cardsIds, $cardUsed->card_id);
        }
        $cards = Card::all()->except($cardsIds)->toArray();
        $random = array_rand($cards);
        return $cards[$random];
    }


    public function calculateTotal($isPlayer, $play){
        if($isPlayer)
            $cards = DB::table('player_cards')->select('card_id','card_value')->where('play_id', $play->id)->orderBy('created_at')->get();
        else
            $cards = DB::table('casino_cards')->select('card_id','card_value')->where('play_id', $play->id)->orderBy('created_at')->get();
        $hasAce = false;
        $total = 0;
        foreach ($cards as $card){
            if($card->card_value === 11) $hasAce = true;
            $total+= $card->card_value;
        }
        if($total > 21 && $hasAce){
            $this->turnAceToOne($isPlayer, $play);
            $this->calculateTotal($isPlayer, $play);
        }else{
            if($isPlayer)
                $play->playerTotal = $total;
            else
                $play->casinoTotal = $total;
            $play->save();
        }
    }

    public function turnAceToOne($isPlayer, $play){
        if($isPlayer){
            $Ace = PlayerCard::where('play_id',$play->id)->where('card_value', 11)->first();
            $Ace->card_value = 1;
            $Ace->save();
        }else{
            $Ace = CasinoCard::where('play_id',$play->id)->where('card_value', 11)->first();
            $Ace->card_value = 1;
            $Ace->save();
        }
    }

}
