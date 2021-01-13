<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Classes\Cron\Cron;
use App\Helpers\Responder;

class CronController extends Controller
{
    public function getStatus(Request $request)
    {
        $cron = new Cron();
        $check = $cron->isUserAllowed(Auth::user()->username);
        if (!$check) {
            return response()->json(Responder::build(200,true, "Cron not allowed for this user.",["status" => false],"Cron not allowed for this user."), 200);
        }
        return response()->json(Responder::build(200,true, "Cron allowed for this user.",["status" => true],"Cron  allowed for this user."), 200);
    }
    public function turnOn(Request $request)
    {
        $cron = new Cron();
        $check = $cron->isUserAllowed(Auth::user()->username);
        if ($check) {
            return response()->json(Responder::build(400,false, "Cron already turned on for this user.",["status" => false],"Cron not allowed for this user."), 400);
        }
        $allowed = $cron->allowUser(Auth::user()->username);
        if ($allowed) {
            return response()->json(Responder::build(200,true, "Cron turned on for this user.",[],"Cron  allowed for this user."), 200);
        }
        return response()->json(Responder::build(500,true, "Cannot able to turn on cron.",[],"Cron cannot be allowed for this user."), 500);
    }
    public function turnOff(Request $request)
    {
        $cron = new Cron();
        $check = $cron->isUserAllowed(Auth::user()->username);
        if (!$check) {
            return response()->json(Responder::build(400,false, "Cron already turned off for this user.",["status" => false],"Cron not allowed for this user."), 400);
        }
        $disallowed = $cron->disallowUser(Auth::user()->username);
        if ($disallowed) {
            return response()->json(Responder::build(200,true, "Cron turned off for this user.",[],"Cron  disallowed for this user."), 200);
        }
        return response()->json(Responder::build(500,true, "Cannot able to turn off cron.",[],"Cron cannot be disallowed for this user."), 500);
    }
}
