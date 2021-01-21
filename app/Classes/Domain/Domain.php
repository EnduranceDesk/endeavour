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
    public function addMainDomain($domain_without_www, $username)
    {
        $ip_address = (new Server)->get();
        $dns = new DNS();
        $check = $dns->addDomain($ip_address, $domain_without_www);
        if (!$check) {
            return false;
        }
        $apache = new Apache;
        $check = $apache->addMainDomain($domain_without_www, $username);
        return $check;
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
}