<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Input;
use ZammadAPIClient\Client;
use App\Userlog;

//  do a id check

class KycController extends Controller {

    //
    public function __construct() {
        $this->middleware('auth');
    }

    public function kyc() {

        $data['kyc_token'] = "";
        $data['submitted'] = "";
        $data['error'] = "";
        return view("kyc", $data);
    }

    public function kyctoken($token) {
        $data = $this->PostKyc($token);
        if (empty($data['result'])) {
            $data['error'] = "No token match";
            $data["submitted"] = "";
        } else {
            $data['error'] = "";
        }
        return view("kyc", $data);
    }

    public function PostKyc($token = null) {

        $apiurl = 'https://support.hdcoin.co';
        $versionapi = '/api/v1/';
        $type = "eth";
        $input = Input::all();
        $client = new Client([
            'url' => "", // URL to your Zammad installation
            'username' => '', // Username to use for authentication
            'password' => '', // Password to use for authentication)
        ]);

        if (isset($input['kyc_token']) || $token != null) {
            if ($token == null) {
                $token = $input['kyc_token'];
            }

            $response = Curl::to(url . 'address/' . $type . '/kyc_status/' . $token)->get();
            $data = json_decode($response, true);
            if ($data['result'] == "") {
                $type = "btc";
                $response = Curl::to(url . 'address/' . $type . '/kyc_status/' . $token)->get();

                $data = json_decode($response, true);
            }


            if (!empty($data['submitted'])) {
                $url = $apiurl . $versionapi . "tickets/search?query=" . $token . "&limit=10&expand=true";
                $tickets = $client->get($url)->getData();
                $url = $apiurl . $versionapi . "ticket_articles/" . $tickets[0]["article_ids"][0];
                $ticketaricles = $client->get($url)->getData();
                $data['articles'] = $ticketaricles;
                foreach ($data['articles']['attachments'] as $key => $at) {
                    $data['articles']['attachments'][$key]['url'] = $apiurl . $versionapi . "ticket_attachment/" . $data['articles']['ticket_id'] . "/" . $data['articles']['id'] . "/" . $at['id'];
                }
            } else {
                
            }
        }
        if (isset($input['reject'])) {
            $response = Curl::to(url . 'address/' . $type . '/kyc_rejected/' . $token)->get();
            $url = $apiurl . $versionapi . "ticket_articles/";
            $content = $input['reject_reason'];
            $subject = "KYC documents rejected";
            $ticketID = $data['articles']['ticket_id'];
            $email = $data['articles']['to'];
            $response = $client->post($url, $this->ReplyTicket($ticketID, $subject, $content, $email));


            $userlog = new Userlog;
            $userlog->user_id = Auth::id();
            $userlog->token = $token;
            $userlog->description = "Reject the Kyc";
            $userlog->save();


            return redirect('kyc/' . $token);
        } elseif (isset($input['accept'])) {
            $response = Curl::to(url . 'address/' . $type . '/kyc_accepted/' . $token)->get();
            $url = $apiurl . $versionapi . "ticket_articles/";
            $content = "Your KYC documents have been accepted.";
            $subject = "KYC documents accepted";
            $ticketID = $data['articles']['ticket_id'];
            $email = $data['articles']['to'];
            $response = $client->post($url, $this->ReplyTicket($ticketID, $subject, $content, $email));

            $userlog = new Userlog;
            $userlog->user_id = Auth::id();
            $userlog->token = $token;
            $userlog->description = "Accept the Kyc";
            $userlog->save();

            return redirect('kyc/' . $token);
        } else {
            
        }
        $data['kyc_token'] = $token;
        return $data;
    }

/*
reply ticket function
*/
    protected function ReplyTicket($ticketid, $subject, $content, $email) {
        $ticketReply = [
            'ticket_id' => $ticketid,
            'to' => $email,
            'cc' => '',
            'subject' => $subject,
            "body" => $content,
            "content_type" => "text/html",
            "type" => "note",
            "internal" => false,
            "time_unit" => "12"
        ];
        return $ticketReply;
    }

}
