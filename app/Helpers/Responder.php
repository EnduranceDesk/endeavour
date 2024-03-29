<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

/**
 * Responder
 */
class Responder
{
    public static function build(int $response_code, bool $success, string $message, array $data = [], string $debug_msg = null)
    {
        $array = [
            'code' => $response_code,
            'success' => $success,
            'message' => $message,

            'data' => $data
        ];
        // if (config("app.debug") === true) {
            $array = array_merge($array, ['debug_msg' => $debug_msg]);
            // }
        Log::alert($message, $array);
        return $array;
    }
}
