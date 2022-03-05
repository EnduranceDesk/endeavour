<?php

namespace App\Http\Controllers\API\Auth;

use App\Classes\LinuxUser\LinuxUser;
use App\Classes\MySQL\MySQL;
use App\Helpers\Responder;
use App\Helpers\Screen;
use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function changePassword(Request $request)
    {
        $username = strtolower($request->input("username"));
        $password = $request->input("password");
        if ((!$request->input("username")) || (!$request->input("password")) ) {
            return response()->json(Responder::build(400,false, "Bad Request",[],"username or password not supplied"), 400);
        }
        if (!User::where("username", $request->input("username"))->first()) {
            return response()->json(Responder::build(400,false, "User doesn't exist!"), 400);
        }
        if (!LinuxUser::validateUsername($request->input("username"))) {
            return response()->json(Responder::build(400,false, "User with this username already exist.", [], "User with this username already exist as per linux."), 400);
        }

        $userModel = Auth::user();
        if  (($userModel->username != "root") && ($username != $userModel->username)) {
            return response()->json(Responder::build(400,false, "Insufficient privileges", [], "Insufficient privileges. Rover can only change his/own passwords."), 400);
        }

        foreach(User::where("username", $username)->first()->domains as $domain) {
            foreach($domain->emails as $email) {
                $salt = substr(sha1(rand()), 0, 16);
                $email->password = crypt($password, "$6$$salt");
                $email->save();
            }
        }


        $mySql = new MySQL(config("database.connections.mysql.host"), config("database.connections.mysql.username"), config("database.connections.mysql.password"), "mysql");
        $oldmysqlpassword  = $mySql->getCurrentRootPassword();
        if ($username == "root" && $userModel->username == "root") {
            $process = Screen::get()->executeFileNow(base_path("shell_scripts/change_root_passwords.shell"), [$username,$oldmysqlpassword, $password], null, 20);
            if ($process->success) {
                return response()->json(Responder::build(200,true, "All tokens updated successfully", [], "All Tokens changed for $username"), 200);
            }
        } elseif (!empty($userModel->username) && $username != "root") {
            $mySql = new MySQL(config("database.connections.mysql.host"), config("database.connections.mysql.username"), config("database.connections.mysql.password"), "mysql");
            $mySql->changeNonRootUserPassword($username, $password);
            $process = Screen::get()->executeFileNow(base_path("shell_scripts/change_linux_user_password.shell"), [$username, $password], null, 20);
            if ($process->success) {
                return response()->json(Responder::build(200,true, "All tokens updated successfully", [], "All Tokens changed for $username"), 200);
            }
        } else {
            return response()->json(Responder::build(400,false, "Unknown case", [], "Unknown case for password change for $username."), 400);
        }
        return response()->json(Responder::build(400,false, "Failed to change password.", [], "Failed to change password."), 400);

    }
}
