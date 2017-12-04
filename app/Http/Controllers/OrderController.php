<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use LogController;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Input;
use ZammadAPIClient\Client;
use App\Userlog;

const url = "http://icowallet:5000/";

class OrderController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    protected function canncelOrder($type, $user_token) {
        $response = Curl::to(url . 'address/' . $type . '/cancel/' . $user_token)->get();
    }

    public function getorders(Request $request, $type) {
      
        if ($request->input('limit')) {
            $limit = $request->input('limit');
        } else {
            $limit = 10;
        }
        if ($request->input('offset')) {
            $offset = $request->input('offset');
        } else {
            $offset = 0;
        }
        if ($request->input('status')) {
            $status = $request->input('status');
        } else {
            $status = "";
        }

        if ($request->input('page')) {
            $page = $request->input('page');
            $curentpage = $request->input('page') + 1;
            $offset = $request->input('page') * $limit;
        } else {
            $page = 0;
            $curentpage = 1;
            $offset = 0;
        }

        if (!empty($request->input())) {
            $parm = "?";
            foreach ($request->input() as $key => $row) {
                if ($key !== "ip" && $key != "cancelpage") {
                    $parm .= $key . "=" . $row . "&";
                }
                if ($key == 'page') {
                    $parm .= "offset" . "=" . $row * $limit . "&";
                }
            }
        } else {
            $parm = "?limit=10&offset=0";
        }

        if ($request->input('ip')) {
            $response = Curl::to(url . 'status/' . $type . '/ip/' . $request->input('ip') . $parm)->get();
        } else {
            $response = Curl::to(url . 'status/' . $type . '/all' . $parm)->get();
        }
        $orders = json_decode($response, true);
        if ($request->input('cancelpage') == 1) {

            foreach ($orders[$type . "_addrs"] as $row) {
                if (strpos($row['status'], "pending") !== false) {
                    $this->canncelOrder($type, $row['user_token']);
                    $userlog = new Userlog;
                    $userlog->user_id = Auth::id();
                    $userlog->token = $row['user_token'];
                    $userlog->description = "Order Cancelled";
                    $userlog->save();
                }
            }

            return redirect("orders/" . $type . $parm . "ip=" . $request->input('ip'));
        }

        $total = $orders[$type . '_count'];
        $totalpages = ceil($total / $limit);
        $pagebutton = "";

        $extendbutton = false;
        if ($page + 5 > $totalpages) {
            $pageend = $totalpages;
        } else {
            $pageend = $page + 5;
            $extendbutton = true;
        }
        if ($page < 5) {
            $page = 0;
        }

        for ($i = $page; $i < $pageend; $i++) {
            $k = $i + 1;
            if ($request->input('page') == $i)
                $active = "active";
            else
                $active = "";

            $pagebutton .= "<li class='paginate_button " . $active . "'><a  data-dt-idx=" . $i . " href='#'>" . $k . "</a></li>";
        }
        if ($extendbutton) {
            $pagebutton .= "<li class='paginate_button'><a class='extendbutton' href='#'>...</a></li>";
        }
//
        $data = array(
            'type' => $type,
            'title' => $type . " ",
            "datas" => $orders[$type . '_addrs'],
            "pagebutton" => $pagebutton,
            "limit" => $limit,
            "status" => $status,
            "totalpages" => $totalpages,
            "page" => $page,
            'curentpage' => $curentpage,
            "total" => $total,
            "ip" => $request->input('ip'),
            "first" => $offset == 0 ? "disabled" : "",
            "last" => $offset + 1 == $totalpages ? "disabled" : "",
        );
        return view('pending', $data);
    }

    public function cancelip() {
        $ip = $_POST['ipremove'];
        $type = $_POST['type'];
        $response = Curl::to(url . "status/" . $type . "/ip/" . $ip)->get();
        $datas = json_decode($response, true);
        foreach ($datas[$type . "_addrs"] as $row) {
            if (strpos($row['status'], "pending") !== false) {
                $this->canncelOrder($type, $row['user_token']);
                $userlog = new Userlog;
                $userlog->user_id = Auth::id();
                $userlog->token = $row['user_token'];
                $userlog->description = "Order Cancelled";
                $userlog->save();
            }
        }
        return redirect('orders/' . $type);
    }

    public function cancelpending($type, $token) {
        $response = Curl::to(url . "address/" . $type . "/cancel/" . $token)->get();
        $this->canncelOrder($type, $token);
        $userlog = new Userlog;
        $userlog->user_id = Auth::id();
        $userlog->token = $token;
        $userlog->description = "Order Cancelled";
        $userlog->save();
        return redirect('orders/' . $type);
    }

}
