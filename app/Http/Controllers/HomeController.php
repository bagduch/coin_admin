<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Input;
use ZammadAPIClient\Client;
use App\Userlog;


class HomeController extends Controller {

    protected $data;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    private function satoshis_to_btc($satoshis) {
        $SATOSHIS = "100000000";
        $btc = bcdiv((string) $satoshis, $SATOSHIS, 8);
        $btc = rtrim($btc, '0');
        $btc = rtrim($btc, '.');
        return $btc;
    }

    private function wei_to_eth($wei) {
        $WEI = "1000000000000000000";
        $eth = bcdiv((string) $wei, $WEI, 18);
        $eth = rtrim($eth, '0');
        $eth = rtrim($eth, '.');
        return $eth;
    }

    private function bcround($num, $scale) {
        return bcadd($num, "0", $scale);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $title = "Dashboard";
        $response = Curl::to(url . 'stats/sales_summary')
                ->get();

        $response = json_decode($response, true);

        if (!empty($response)) {
            $response['btc_confirmed'] = self::bcround($response['btc_confirmed'], 4);
            $response['eth_confirmed'] = self::bcround($response['eth_confirmed'], 4);

            $data = array(
                "title" => $title,
                "response" => $response,
            );

            return view('home', $data);
        } else {
            echo "ICO Wallet Not connect";
        }
    }

    /**
     * Show the USD rates for BTC and ETH.
     *
     * @return \Illuminate\Http\Response
     */
    public function rates(Request $request) {
        if ($request->isMethod('post')) {
            // only admin role can modify this (see routes/web.php)
            $input = array('auto' => $request->input('price_auto'), 'btcusd' => $request->input('btcusd'), 'ethusd' => $request->input('ethusd'));
            $response = Curl::to(url . 'price/auto_set?' . http_build_query($input))->get();
        }

        $title = 'USD Rates';
        $response = Curl::to(url . 'price/btcusd')->get();
        $btcusd = json_decode($response, true)['price'];
        $response = Curl::to(url . 'price/ethusd')->get();
        $ethusd = json_decode($response, true)['price'];
        $response = Curl::to(url . 'price/auto_get')->get();
        $price_auto = json_decode($response, true)['auto'];
        $data = array(
            'btcusd' => self::bcround((string) $btcusd, 2),
            'ethusd' => self::bcround((string) $ethusd, 2),
            'price_auto' => $price_auto);
        $data = array(
            'title' => $title,
            'response' => $data,
        );
        return view('rates', $data);
    }

    /*
     * kyc check
     * 
     */

   


    /**
     * Show an order status
     *
     * @return \Illuminate\Http\Response
     */
    public function order_status(Request $request) {
        $title = 'Order Status';
        $type = "btc";
        $user_token = "";
        $order = null;

        if ($request->isMethod('post')) {
            $user_token = $request->input('user_token');
            $response = Curl::to(url . 'address/' . $type . '/status/' . $user_token)->get();
            $title = 'Order Status - ' . $user_token;
            $order = json_decode($response, true);

            if (!isset($order)) {
                $type = "eth";
                $response = Curl::to(url . 'address/' . $type . '/status/' . $user_token)->get();
                $order = json_decode($response, true);
            }
        }

        $data = array(
            'type' => $type,
            'user_token' => $user_token,
            'order' => $order);
        $data = array(
            'title' => $title,
            'response' => $data,
        );
        return view('order_status', $data);
    }

    /**
     * Show the BTC and ETH xpubkeys.
     *
     * @return \Illuminate\Http\Response
     */
    public function xpub(Request $request) {
        // only admin role can see this (see routes/web.php)

        $title = 'Xpub keys';
        $response = Curl::to(url . 'status/xpub')->get();
        $data = json_decode($response, true);
        $data = array(
            'title' => $title,
            'response' => $data,
        );
        return view('xpub', $data);
    }

    /**
     * Show various server statuses.
     *
     * @return \Illuminate\Http\Response
     */
    public function servers(Request $request) {
        $title = 'Servers';
        $response = Curl::to(url . 'status/servers')->get();
        $data = json_decode($response, true);
        $data['last_payment_check'] = date("Y-m-d H:i:s", $data['last_payment_check']);
        $data['last_expiry_check'] = date("Y-m-d H:i:s", $data['last_expiry_check']);
        $data = array(
            'title' => $title,
            'response' => $data,
        );
        return view('servers', $data);
    }

}
