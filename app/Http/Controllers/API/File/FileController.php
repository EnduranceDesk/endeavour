<?php

namespace App\Http\Controllers\API\File;

use App\Helpers\Responder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{

    public function getContent(Request $request)
    {
        if (Auth::user()->username != "root") {
            return response()->json(Responder::build(400,false, "Bad Request",[],"Only root can read files"), 400);
        };
        if (!$request->input("filepath")) {
            return response()->json(Responder::build(400,false, "Bad Request",[],"File path not provided!"), 400);
        }
        if (!file_exists($request->filepath)) {
            return response()->json(Responder::build(400,false, "Bad Request",[],"File does not exist!"), 400);
        }
        return response()->json(Responder::build(200,true, "Content fetched",["content" => file_get_contents($request->filepath)],"Only root can read files"), 200);
    }
}
