<?php

namespace App\Classes\PHP;

/**
 * PHP Manager
 */
class PHP
{
    protected $path = "/etc/endurance/configs/php";
    public function getVersions()
    {

        $dirs = array_diff(scandir($this->path), array('..', '.'));
        $versions = [];
        foreach($dirs as $dir) {
            if (stripos($dir, "endurance") > -1) {
                continue;
            }
            $versions[] = $dir;
        }
        return $versions;
    }
    public function getServiceName($version)
    {
        return str_replace("fpm", "php-fpm", $version);
    }
    public function getRemiName($version)
    {
        return str_replace("-fpm", "", $version);
    }
}
