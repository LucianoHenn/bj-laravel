<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_cards', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->tinyInteger('card_order');
            $table->unsignedBigInteger('play_id');
            $table->foreign('play_id')->on('plays')->references('id');
            $table->unsignedBigInteger('card_id');
            $table->foreign('card_id')->on('cards')->references('id');
            $table->tinyInteger('card_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_cards');
    }
};
