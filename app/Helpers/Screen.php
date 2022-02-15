<?php

namespace App\Helpers;

class Screen {

    public static function get()
    {
        return new \myPHPnotes\Screen(storage_path("screen"), new \App\Models\Process());
    }
}
