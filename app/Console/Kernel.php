<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Ixudra\Curl\Facades\Curl;
use App\Coin;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
            //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->call(function() {
            $response = Curl::to('http://icowallet:5000/stats/sales_summary')
                    ->get();
            $response = json_decode($response, 1);
            $coin = new Coin();
            foreach ($response as $key => $row) {
                if ($key != "satoshis_confirmed" && $key != "satoshis_pending" && $key != "wei_confirmed" && $key != "wei_pending") {
                    $coin->$key = (float) $row;
                }
            }
            $coin->save();
        })->everyTenMinutes();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands() {
        require base_path('routes/console.php');
    }

}
