<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Userlog;

class LogController extends Controller {

    //
    public function __construct() {
        $this->middleware('auth');
    }

    public function setLog($userid, $message) {

        $userlog = new Userlog;
        $userlog->user_id = $userid;
        $userlog->description = $message;
        $userlog->save();
    }

    public function showLog() {

        $log = Userlog::with('user')->get();
        $data = array(
            "title" => "Logs",
            "datas" => $log
        );

        return view('log', $data);
    }

}
