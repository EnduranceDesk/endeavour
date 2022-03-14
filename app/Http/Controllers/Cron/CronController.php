<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Classes\Cron\Cron;
use App\Helpers\Responder;
use App\Models\Rover;
use App\Models\User;

class CronController extends Controller
{
    public function addEntry(Request $request) {
        $rover = $request->input("rover");
        $command = $request->input("command");
        $current_user = auth()->user();
        $subject_user = User::where("username", $rover)->first()->domains->first();

        if ($current_user->username == "root") {
            $process = Cron::add($rover, $command);
        } elseif ($current_user->username == $rover) {
            $process = Cron::add($rover, $command);
        } else {
            return response()->json(Responder::build(200,false , "Cron entry addition failed due to invalid permissions.",[],"Cron entry addition failed.. Rover: " . $rover . " | Command: " . $command .  " | Linux user: " . $current_user->username), 200);
        }
        if ($process->success) {
            return response()->json(Responder::build(200,true , "Cron entry addition successful.",[],"Cron entry addition successful. Rover: " . $rover . " | Command: " . $command .  " | Linux user: " . $current_user->username), 200);
        } else {
            return response()->json(Responder::build(200,false , "Cron entry addition failed.",[],"Cron entry addition failed.. Rover: " . $rover . " | Command: " . $command .  " | Linux user: " . $current_user->username), 200);
        }
    }
    public function deleteEntry(Request $request) {
        $rover = $request->input("rover");
        $command = $request->input("command");
        $current_user = auth()->user();
        $subject_user = User::where("username", $rover)->first()->domains->first();

        if ($current_user->username == "root") {
            $process = Cron::delete($rover, $command);
        } elseif ($current_user->username == $rover) {
            $process = Cron::delete($rover, $command);
        } else {
            return response()->json(Responder::build(200,false , "Cron entry deletion failed due to invalid permissions.",[],"Cron entry addition failed.. Rover: " . $rover . " | Command: " . $command .  " | Linux user: " . $current_user->username), 200);
        }
        if ($process->success) {
            return response()->json(Responder::build(200,true , "Cron entry deletion successful.",[],"Cron entry addition successful. Rover: " . $rover . " | Command: " . $command .  " | Linux user: " . $current_user->username), 200);
        } else {
            return response()->json(Responder::build(200,false , "Cron entry deletion failed.",[],"Cron entry addition failed.. Rover: " . $rover . " | Command: " . $command .  " | Linux user: " . $current_user->username), 200);
        }
    }
    public function getEntries(Request $request)
    {
        $rover=$request->input("rover");
        $current_user = auth()->user();
        if ($current_user->username == "root") {
            $text = Cron::fetch($rover);
        } elseif ($current_user->username == $rover) {
            $text = Cron::fetch($rover);
        } else {
            return response()->json(Responder::build(200,false , "Cron entry fetch failed.",[],"Cron entry fetch failed. Rover: " . $rover), 200);
        }
        if (empty($text)) {
            $entries = [];
        } else {
            $entries  = explode(PHP_EOL, $text);
        }
        $entries = array_filter($entries);
        $entries = array_filter($entries, function($v, $k) {
            return  $v != " ";
        }, ARRAY_FILTER_USE_BOTH);
        return response()->json(Responder::build(200,true , "Cron entry fetch successful.",[
            "entries" => $entries
        ],"Cron entry fetch successful."), 200);

    }
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
