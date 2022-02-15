<?php

namespace App\Http\Controllers\API\PHP;

use App\Classes\PHP\PHP;
use App\Helpers\Responder;
use App\Http\Controllers\Controller;
use App\Models\Domain as DomainEloquent;
use Illuminate\Http\Request;

class PHPController  extends Controller {
    public function getVersions()
    {
        $versions =  (new PHP)->getVersions();
        return response()->json(Responder::build(200,true, "PHP versions",["php" =>$versions],"PHP Versions"), 200);
    }
    public function changePHPVersion(Request $request)
    {
        $domain = strtolower($request->input("domain"));
        $php_version = $request->input("php_version");
        if ( (!$request->input("domain")) || (!$request->input("php_version")) ) {
            return response()->json(Responder::build(400,false, "Bad Request",[],"Domain or version not supplied"), 400);
        }
        $domainModel = DomainEloquent::where("name", $domain)->first();
        if (! $domainModel ) {
            return response()->json(Responder::build(400,false, "Bad Request",[],"Cannot find the domain $domain for the user."), 400);
        }
        $currentPHP = json_decode($domainModel->metadata)->current_php;
        $changed = (new PHP)->changePHPVersion($domain, $currentPHP,  $php_version);
        if (!$changed) {
            return response()->json(Responder::build(400,false, "Bad Request",[],"Cannot change the PHP version."), 400);
        }
        $metadata = json_decode($domainModel->metadata);
        $metadata->current_php = $php_version;
        $domainModel->metadata = json_encode($metadata);
        $domainModel->save();
        return response()->json(Responder::build(200,true, "PHP version of $domain changed to $php_version.",[],"PHP version changed."), 200);
    }
}
