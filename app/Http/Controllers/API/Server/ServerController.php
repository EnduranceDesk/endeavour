<?php

namespace App\Http\Controllers\API\Server;

use App\Classes\Server\Server;
use App\Helpers\Responder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function setIP(Request $request)
    {
        $ip = $request->input("ip");
        if (!$request->input("ip")) {
            return response()->json(Responder::build(400,false, "Bad Request",[],"ip not supplied"), 400);
        }
        $server = new Server; 
        $check = $server->add($ip);
        if (!$check) {
            return response()->json(Responder::build(500,false, "Unable to perform IP change operation.",[],"IP register ip to the server"), 500);
        }
        return response()->json(Responder::build(200,true, "IP changed.",[],"IP changed."), 200);
    }
    public function getIP(Request $request)
    {
        $server = new Server; 
        $ip = $server->get();
        if (!$ip) {
            return response()->json(Responder::build(200,false, "Server IP not set.",[],"Server IP not set."), 500);
        }
        return response()->json(Responder::build(200,true, "IP Fetched.",["ip" => $ip],"IP fetched."), 200);
    }
}
