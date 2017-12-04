<?php

use Illuminate\Database\Seeder;
use App\Coin;
use App\Role;

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        // $this->call(UsersTableSeeder::class);

        /**
         * 
         *    $table->float('btc_address_count');
          $table->float('btc_confirmed');
          $table->float('btc_pending');
          $table->float('coins_sold_payment_confirmed');
          $table->float('coins_sold_pending_payment_confirmation');
          $table->float('email_count');
          $table->float('eth_address_count');
          $table->float('eth_confirmed');
          $table->float('eth_pending');
         * 
         * 
         */
        for ($i = 0; $i < 50; $i++) {
            Coin::create([
                'btc_address_count' => $i,
                'btc_confirmed' => $i,
                'btc_pending' => $i,
                'coins_sold_payment_confirmed' => $i,
                'coins_sold_pending_payment_confirmation' => $i,
                'email_count' => $i,
                'eth_confirmed' => $i,
                'eth_address_count' => $i,
                'eth_pending' => $i,
            ]);
        }


    }

}
