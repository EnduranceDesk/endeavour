<?php

namespace App\Classes\Acme;

use App\Classes\Apache\Apache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;


/**
 * Acme.sh
 */
class Acme 
{
    /*
        Error code 2 : When the ceritificates are generated too frequently and the request to regenerate is dropped/skipped by acme.
     */
    
    public function performAcme($domain_without_www)
    {
        $subs = [
            'www', 'mail'
        ];
        $array = [
            "sh",
            "/etc/endurance/executables/acme.sh/acme.sh",
            "--issue",
            "--apache",
            "-d",
            $domain_without_www
        ];
        foreach ($subs   as $sub) {
            $array[] = "-d";
            $array[] = $sub. "." . $domain_without_www ;
        }
        // $array[] = "--force";
        $process = new Process($array);
        $process->start();

        $iterator = $process->getIterator($process::ITER_SKIP_ERR | $process::ITER_KEEP_OUTPUT);
        $output = "";
        foreach ($iterator as $data) {
            $output .= $data."\n";
        }
        if ($process->getExitCode() === 0 || $process->getExitCode() === 2) {
            return $this->process($domain_without_www);
        } else {
            $loggable = "Error while performing acme. Error:  " . $process->getErrorOutput() .  ", Error code: " .  $process->getExitCode() ;
            Log::error($loggable);
            throw new \Exception($loggable );
            return false;
        }
    }
    public function buildChain($domain_without_www)
    {
        $key = file_get_contents("/root/.acme.sh/" . $domain_without_www . "/" . $domain_without_www . ".key");
        $cert = file_get_contents("/root/.acme.sh/" . $domain_without_www . "/" . $domain_without_www . ".cer");
        $ca = file_get_contents("/root/.acme.sh/" . $domain_without_www . "/ca.cer");
        $chain = trim($key) .  PHP_EOL  . PHP_EOL  . trim($cert) . PHP_EOL . PHP_EOL . trim($ca);
        return $chain;
    }
    public function process($domain_without_www)
    {
        $chain  = $this->buildChain($domain_without_www);
        $apache = new Apache;
        $check = $apache->updateSSL(Auth::user()->username, $domain_without_www , $chain);
        return $check;
    }
}