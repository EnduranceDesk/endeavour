<?php

namespace App\Classes\Acme;

use App\Classes\Apache\Apache;
use App\Helpers\Screen;
use App\Models\Domain;
use Exception;
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

    public function performAcme($domain_without_www, $current_php_version)
    {
        $process = Screen::get()->executeFileNow(base_path("shell_scripts/process_acme_ssl.shell"), [$domain_without_www], null, 300);
        if (!$process->success) {
            throw new Exception("Failed to perform Acme");
        }
        return $this->process($domain_without_www, $current_php_version);
    }
    public function buildChain($domain_without_www)
    {
        $key = file_get_contents("/root/.acme.sh/" . $domain_without_www . "/" . $domain_without_www . ".key");
        $cert = file_get_contents("/root/.acme.sh/" . $domain_without_www . "/" . $domain_without_www . ".cer");
        $ca = file_get_contents("/root/.acme.sh/" . $domain_without_www . "/ca.cer");
        $chain = trim($key) .  PHP_EOL  . PHP_EOL  . trim($cert) . PHP_EOL . PHP_EOL . trim($ca);
        return $chain;
    }
    public function process($domain_without_www, $current_php_version)
    {
        $chain  = $this->buildChain($domain_without_www);
        $apache = new Apache;
        $domainModel = Domain::where("name", $domain_without_www)->first();

        $check = $apache->updateSSL($domainModel->user->username, $domain_without_www , $chain, $current_php_version);
        return $check;
    }
}
