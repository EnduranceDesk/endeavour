<?php

namespace App\Http\Controllers\Domain;

use App\Classes\Apache\Apache;
use App\Helpers\Responder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DomainController extends Controller
{
    public function getMyDomains()
    {
        $domains = Auth::user()->domains->each->append("ssl");
        return response()->json(Responder::build(200,true, "Domains Fetched.",["domains" => $domains],"Rover Domains Fetched."), 200);
    }
    public function updateSSL(Request $request)
    {
        $domain =  $request->input("domain");
        $chain = $request->input("chain");
        if (!$domain or !$chain) {
            return response()->json(Responder::build(400,true, "Domain or chain not provided.",[],"Domain or chain not provided."), 400);
        }
        $domain = Auth::user()->domains->where("name", $domain)->first();
        if (!$domain) {
            return response()->json(Responder::build(400,true, "Domain not found.",[],"Domain not found."), 400);
        }
        $apache = new Apache;
        $check = $apache->updateSSL(Auth::user()->username, $domain->name , $chain);
        if ($check) {
            return response()->json(Responder::build(200,true, "Domain SSL updated.",[],"Domain SSL Updated."), 200);
        }
        return response()->json(Responder::build(500,true, "Unable to update Domain SSL.",[],"Unable to update Domain SSL."), 500);
    }
}
