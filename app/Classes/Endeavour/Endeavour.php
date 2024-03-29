<?php

namespace App\Classes\Endeavour;

/**
 * Enveadour Mission Control Wrapper
 */
class Endeavour
{
    protected $host = "http://142.93.219.1:1023";
    protected $token;
    function __construct($token = null)
    {
        $this->token = $token;
    }
    public function login($username, $password) {
        $response = $this->post($this->host . "/raven/login", ['username'=>$username, 'password'=> $password]);
        return $response;
    }
    protected function setToken($token)
    {
        $this->token = $token;
    }
    public function buildRover($username, $domain, $password)
    {
        $response = $this->post($this->host . "/raven/rover/build", ['username'=>$username, 'password'=> $password, 'domain' => $domain]);
        return $response;
    }
    public function setServerIP($ip)
    {
        $response = $this->post($this->host . "/raven/server/ip/set", ['ip'=>$ip]);
        return $response;
    }
    protected function post($url, $parameters)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS ,$parameters);
        if ($this->token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->token, 
            ));
        } 
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result);
    }

}