<?php

namespace App\Classes\Domain;

use App\Classes\Acme\Acme;
use App\Classes\Apache\Apache;
use App\Classes\DNS\DNS;
use App\Classes\Server\Server;

/**
 * Domain
 */
class Domain
{
    public function addMainDomain($domain_without_www, $username, $php_version)
    {
        $ip_address = (new Server)->get();
        $dns = new DNS();
        $check = $dns->addDomain($ip_address, $domain_without_www);
        if (!$check) {

            throw new \Exception("Domain zone file cannot be created", 1);
            return false;
        }
        $apache = new Apache;
        $check = $apache->addMainDomain($domain_without_www, $username, $php_version);
        if (!$check) {
            $dns->removeDomain($domain_without_www);
            throw new \Exception("Domain vhost file cannot be created", 1);
            return false;
        }
        return $check;
    }
    public function removeMainDomain($domain_without_www, $username, $php_version)
    {
        $apache = new Apache;
        $dns = new DNS();

        $dns->removeDomain($domain_without_www);
        $apache->removeMainDomain($domain_without_www, $username, $php_version);
        return true;

    }
    public function performAutoSSL($domain_without_www)
    {
        $acme = new Acme;
        try {
            $performed = $acme->performAcme($domain_without_www);
        } catch(\Exception $e) {
            throw $e;
            return false;
        }
        if (!$performed) {
            return false;
        } else {
            return $performed;
        }
    }
    public function changePHPversion($domain_without_www)
    {
        # code...
    }
}
