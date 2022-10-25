<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $suits = ['clubs', 'spades', 'hearts', 'diamonds'];

        for ( $i = 0; $i < 4; $i++) {
        for ($j = 1; $j <= 13; $j++) {
         DB::table('cards')->insert([
             'suit' => $suits[$i],
             'number' => $j
         ]);
      }
    }


    }
}
