<?php

namespace App\Http\Controllers\API\Rover;

use App\Classes\Domain\Domain;
use App\Classes\LinuxUser\LinuxUser;
use App\Classes\MySQL\MySQL;
use App\Classes\PHP\PHP;
use App\Classes\Server\Server;
use App\Helpers\Responder;
use App\Http\Controllers\Controller;
use App\Models\Domain as DomainEloquent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoverController extends Controller
{
    public function list(Request $request)
    {
        $users = User::with("domains")->rovers()->get()->toArray();
        return response()->json(Responder::build(200,true, "Rover(s) retracted",$users,"Rover(s) retracted."), 200);
    }
    public function destroy(Request $request)
    {
        $username = $request->input("username");

        if ((!$request->input("username"))) {
            return response()->json(Responder::build(400,false, "Bad Request",[],"username not supplied"), 400);
        }

        $linuxUserRemoval = LinuxUser::remove($username);
        if (!$linuxUserRemoval) {
            return response()->json(Responder::build(200,false, "Cannot remove the user from Linux OS."), 200);
        }
        $mysql = new MySQL(config("database.connections.mysql.host"), config("database.connections.mysql.username"), config("database.connections.mysql.password"), "mysql");
        $mysql->removeUserSet($username);


        $user = User::where("username", $username)->rovers()->first();

        $domainObject = new Domain();
        $domainObject->removeMainDomain($user->domains->first()->name, $username, $user->domains->first()->current_php);
        $user->domains()->delete();
        $user->delete();
        return response()->json(Responder::build(200,true, "Rover destruction successful."), 200);
    }
    public function prepareBuild(Request $request) {
        $versions =  (new PHP)->getVersions();
        return response()->json(Responder::build(200,true, "Prebuilt Metadata",["php" =>$versions],"PHP Versions"), 200);
    }
    public function build(Request $request)
    {

        $username = strtolower($request->input("username"));
        $domain = strtolower($request->input("domain"));
        $password = $request->input("password");
        $php_version = $request->input("php_version");


        if ((!$request->input("username")) || (!$request->input("password")) || (!$request->input("domain")) || (!$request->input("php_version")) ) {
            return response()->json(Responder::build(400,false, "Bad Request",[],"username or password or domain or version not supplied"), 400);
        }
        if (User::where("username", $request->input("username"))->first()) {
            return response()->json(Responder::build(400,false, "Rover with this username already exist."), 400);
        }

        // TODO: Add password based catch to prevent shell injection via password text (with bad command in it)

        if (LinuxUser::validateUsername($request->input("username"))) {
            return response()->json(Responder::build(400,false, "Rover with this username already exist.", [], "Rover with this username already exist as per linux."), 400);
        }

        if (LinuxUser::validateUsername($request->input("username"))) {
            return response()->json(Responder::build(400,false, "Rover with this username already exist.", [], "Rover with this username already exist as per linux."), 400);
        }


        $server = new Server;
        $ip = $server->get();
        if (!$ip) {
            Log::info("ROVER BUILDER: IP NOT SET" );
            return response()->json(Responder::build(400,false, "Server IP not set.", [], "Server IP not set."), 400);
        }

        try {
            Log::info("ROVER BUILDER: Adding linux user" );

            LinuxUser::add($username, $password);
        } catch (\Exception $e) {
            Log::info("ROVER BUILDER: Error adding linux user"  ." Message: ". $e->getMessage());

            return response()->json(Responder::build(500,false, "Error while building rover. ", [], $e->getMessage()), 500);
        }
        if (!LinuxUser::validateUsername($username)) {
            Log::info("ROVER BUILDER:  linux user creation failed" );

            return response()->json(Responder::build(500,false, "Error while building rover. ", [], "Cannot create linux user."), 500);
        }

        try {
            Log::info("ROVER BUILDER:  building mysql user set" );

            $mysql = new MySQL(config("database.connections.mysql.host"), config("database.connections.mysql.username"), config("database.connections.mysql.password"), "mysql");
            $dbSetCreated = $mysql->createUserSet($username, $password);
        } catch (\Exception $e) {
            Log::info("ROVER BUILDER:  error building mysql user set" );

            $mysql = new MySQL(config("database.connections.mysql.host"), config("database.connections.mysql.username"), config("database.connections.mysql.password"), "mysql");
            Log::info("ROVER BUILDER:  rollbacking mysql user set" ." Message: ". $e->getMessage() );

            $mysql->removeUserSet($username);
            try {
                Log::info("ROVER BUILDER:  rollbacking linux user created" );
                LinuxUser::remove($username);
            } catch (\Exception $e) {
                Log::info("ROVER BUILDER:  error while rollbacking linux user created"  ." Message: ". $e->getMessage() );

                return response()->json(Responder::build(500,false, "Error while building rover. ", [], "Cannot remove a linux user which does not even exist."), 500);
            }
            return response()->json(Responder::build(500,false, "Error while building rover. ", [], $e->getMessage()), 500);
        }
        if (!$dbSetCreated) {
            Log::info("ROVER BUILDER:  DB set not created somehow." );
            try {
                Log::info("ROVER BUILDER:  removing linux user" );
                LinuxUser::remove($username);
            } catch (\Exception $e) {
                return response()->json(Responder::build(500,false, "Error while building rover. ", [], "Cannot remove a linux user which does not even exist."), 500);
            }
            return response()->json(Responder::build(500,false, "Error while building rover. ", [], "DB set not created"), 500);

        }
        try {
            Log::info("ROVER BUILDER: adding main domain" );
            $domainObject = new Domain();
            $domainCreated = $domainObject->addMainDomain($domain,$username, $php_version);
        } catch (\Exception $e) {
            Log::info("ROVER BUILDER: error while adding main domain" ." Message: ". $e->getMessage() );
            try {
                LinuxUser::remove($username);
            } catch (\Exception $e) {
                Log::info($e->getMessage());
            }
            try {
                $mysql = new MySQL(config("database.connections.mysql.host"), config("database.connections.mysql.username"), config("database.connections.mysql.password"), "mysql");
                $mysql->removeUserSet($username);
            } catch (\Exception $e) {
                Log::info($e->getMessage());
            }
            $domainObject->removeMainDomain($domain,$username, $php_version);

            return response()->json(Responder::build(500,false, "Error while building rover. ", [], $e->getMessage()), 500);
        }
        if (!$domainCreated) {
            LinuxUser::remove($username);
            $mysql = new MySQL(config("database.connections.mysql.host"), config("database.connections.mysql.username"), config("database.connections.mysql.password"), "mysql");
            $mysql->removeUserSet($username);
            return response()->json(Responder::build(500,false, "Error while building rover. ", [], "Rover creation failed at domain creation level."), 500);
        }
        // Creating DB Entries
        $user = new User;
        $user->name = $username;
        $user->email = $username . "@localhost";
        $user->username =  $username;
        $user->save();

        $domainEloquent = new DomainEloquent;
        $domainEloquent->user_id = $user->id;
        $domainEloquent->name = $domain;
        $domainEloquent->type = "MAIN";
        $domainEloquent->dir = "/home/" .  $username . "/public_html";
        $domainEloquent->mail  = true;
        $domainEloquent->ns1  = true;
        $domainEloquent->ns2  = true;
        $domainEloquent->ftp  = true;
        $domainEloquent->www  = true;
        $domainEloquent->mx  = true;
        $domainEloquent->metadata = json_encode([
            "current_php" => $php_version
        ]);
        $domainEloquent->save();

        return response()->json(Responder::build(200,true, "Rover creation successful."), 200);
    }
}
