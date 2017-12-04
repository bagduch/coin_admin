<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        
    }

    public function summary() {
        
    }

    public function fullGraphic($type) {

        return view("fullgraphic");
    }

    public function Apifullgraphic($type) {
        $btc_graphic = DB::table('coin')->orderBy('created_at', 'asc')->get();


        echo "<pre>", print_r($this->getGraphicline()), "</pre>";
    }

    public function getGraphicline($data, $type, $value) {

        foreach ($data as $key => $row) {
            if ($row->$type == $value) {
                return $row;
                break;
            }
        }
    }

    public function graphic(Request $request, $type) {

        if ($request->input('limit')) {
            $limit = $request->input('limit');
        } else {
            $limit = 20;
        }

        $colorlist = array("#FF6384", "#36A2EB", "#00cc33");
        if ($type == 'btc') {
            $select_data = array("btc_confirmed", "btc_pending", "created_at");
        } elseif ($type == "eth") {
            $select_data = array("eth_confirmed", "eth_pending", "created_at");
        } elseif ($type == "coin") {
            $select_data = array("coins_sold_payment_confirmed", "coins_sold_pending_payment_confirmation", "created_at");
        } elseif ($type == "email") {
            $select_data = array("email_count", "created_at");
        } else {
            $select_data = array("btc_address_count", "eth_address_count", "created_at");
        }
        $btc_graphic = DB::table('coin')->select($select_data)->limit($limit)->orderBy('id', 'desc')->get();
        $graphic = array();
        foreach ($btc_graphic as $row) {
            foreach ($select_data as $data) {
//                $data = date("h:i:s A", strtotime($data));
                $graphic[$data][] = $row->$data;
            }
        }
        $i = 0;
        foreach ($graphic as $key => $row) {
            sort($graphic[$key]);
            if ($key !== "created_at") {
                $name = str_replace("_", " ", $key);
                $name = ucfirst($name);
                $dataset['datasets'][] = array(
                    "label" => $name,
                    "backgroundColor" => $colorlist[$i],
                    "borderColor" => $colorlist[$i],
                    "data" => $graphic[$key],
                    "fill" => false
                );
            }
            $i++;
        }
        $dataset['labels'] = $graphic['created_at'];

        echo json_encode($dataset);
    }

}
