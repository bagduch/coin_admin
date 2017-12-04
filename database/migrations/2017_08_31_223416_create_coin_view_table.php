<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinViewTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('coin', function (Blueprint $table) {
            $table->increments('id');
            $table->float('btc_address_count');
            $table->float('btc_confirmed');
            $table->float('btc_pending');
            $table->float('coins_sold_payment_confirmed');
            $table->float('coins_sold_pending_payment_confirmation');
            $table->float('email_count');
            $table->float('eth_address_count');
            $table->float('eth_confirmed');
            $table->float('eth_pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('coin');
    }

}
