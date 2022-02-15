<?php

namespace App\Classes\Apache;

use App\Classes\PHP\PHP;
use App\Helpers\Screen;

/**
 * Apache Manager
 */
class Apache
{
    protected $apacheuser = "apache";
    protected $apachegroup = "apache";
    protected $othervhostsfiles = "/etc/endurance/configs/discovery/othervhosts.conf";
    protected $vhostdir = "/etc/endurance/configs/discovery/vhosts";
    protected $myssl = "/etc/endurance/configs/discovery/myssl";

    function addMainDomain($domain_without_www, $username, $php_version)
    {
        chmod("/home/" . $username, 0711);
        $public_html = "/home/" . $username .  "/public_html";
        $indexFile = $public_html . DIRECTORY_SEPARATOR . "index.php";
        if (!file_exists($public_html)) {
            mkdir($public_html);
            chmod($public_html, 0750);
            file_put_contents($indexFile, "<?php phpinfo() ?>");
            chmod($indexFile, 0664);
            chown($public_html, $username);
            chgrp($public_html, "apache");
            chown($indexFile, $username);
            chgrp($indexFile, $username);
        }
        $virtualhost = view("templates.apache.NONSSL", ['domain_without_www'=> $domain_without_www, 'username' => $username, 'php_version' => $php_version])->render();
        $vhost_path = $this->vhostdir . DIRECTORY_SEPARATOR . "NONSSL_" . $domain_without_www . ".conf";
        if (file_exists($vhost_path)) {
            throw new \Exception("Domain already added to the server.", 1);
            return false;
        }
        file_put_contents($vhost_path, $virtualhost);

        $phpfpmconfig = view("templates.phpfpm.config", ['domain_without_www'=> $domain_without_www, 'username' => $username, 'apacheuser' => $this->apacheuser, 'apachegroup' => $this->apachegroup, 'php_version' => $php_version])->render();
        $phpfpm_path = "/etc/opt/remi/" . (new PHP)->getRemiName($php_version) .  "/php-fpm.d" . DIRECTORY_SEPARATOR . $domain_without_www . ".conf";
        if (file_exists($phpfpm_path)) {
            unlink($phpfpm_path);
        }

        file_put_contents($phpfpm_path, $phpfpmconfig);

        if (file_exists($phpfpm_path) & file_exists($vhost_path)) {
            $this->rebuildOtherVhosts();
            $this->reload();
            $this->reloadPHP($php_version);
            sleep(2);
            return true;
        }
        unlink($vhost_path);
        unlink($phpfpm_path);
        // $this->reload();
        return false;
    }
    public function removeMainDomain($domain_without_www, $username, $php_version)
    {
        $userroot = "/home/" . $username;
        $vhost_path = $this->vhostdir . DIRECTORY_SEPARATOR . "NONSSL_" . $domain_without_www . ".conf";
        $phpfpm_path ="/etc/opt/remi/" . (new PHP)->getRemiName($php_version) .  "/php-fpm.d". DIRECTORY_SEPARATOR . $domain_without_www . ".conf";
        $ssl_vhost_path = $this->vhostdir . DIRECTORY_SEPARATOR . "SSL_" . $domain_without_www . ".conf";
        if (file_exists($phpfpm_path)) {
            unlink($phpfpm_path);
        }
        if (file_exists($userroot)) {
            $this->removeDirectory($userroot);
        }
        if (file_exists($vhost_path)) {
            unlink($vhost_path);
        }
        if (file_exists($ssl_vhost_path)) {
            unlink($ssl_vhost_path);
        }
        $this->reload();
        $this->reloadPHP($php_version);
        return false;


    }
    public function isSSLVhostExist($domain_without_www)
    {
        $vhost_path = $this->vhostdir . DIRECTORY_SEPARATOR . "SSL_" . $domain_without_www . ".conf";
        if (file_exists($vhost_path)) {
            return true;
        }
        return false;
    }
    public function updateSSL(string $username,string  $domain,string  $chain, $current_php_version)
    {
        if (!file_exists($this->myssl)) {
            mkdir($this->myssl);
        }
        $domainSSLPath = $this->myssl . DIRECTORY_SEPARATOR .$domain ;
        if (file_exists($domainSSLPath)) {
            $this->removeDirectory($domainSSLPath);
        }
        mkdir($domainSSLPath);

        $key = trim(explode(PHP_EOL.PHP_EOL, $chain)[0]);
        $domain_cert = trim(explode(PHP_EOL.PHP_EOL, $chain)[1]);
        $chain = trim(explode(PHP_EOL.PHP_EOL, $chain)[1]) . PHP_EOL.PHP_EOL . trim(explode(PHP_EOL.PHP_EOL, $chain)[2]);

        file_put_contents($domainSSLPath . DIRECTORY_SEPARATOR . $domain . ".key", $key);
        file_put_contents($domainSSLPath . DIRECTORY_SEPARATOR . "ca.cer", $domain_cert);
        file_put_contents($domainSSLPath . DIRECTORY_SEPARATOR . "fullchain.cer", $chain);


        $vhost_path = $this->vhostdir . DIRECTORY_SEPARATOR . "SSL_" . $domain . ".conf";
        if (file_exists($vhost_path)) {
            unlink($vhost_path);
        }
        $virtualhost = view("templates.apache.SSL", ['domain_without_www'=> $domain, 'username' => $username, 'ssldir' => $domainSSLPath . DIRECTORY_SEPARATOR,  'current_php_version' => $current_php_version])->render();
        file_put_contents($vhost_path, $virtualhost);
        if (file_exists($vhost_path)) {
            $this->rebuildOtherVhosts();
            $this->restart();
            return true;
        }
        unlink($vhost_path);
        $this->restart();
        return false;
    }
    public function restart()
    {
        Screen::get()->executeCommand("systemctl restart httpd",[], null,5);
    }
    public function reload()
    {
        Screen::get()->executeCommand("systemctl reload httpd",[], null,5);
    }
    public function restartPHP($version)
    {
        Screen::get()->executeCommand("systemctl restart ". (new PHP)->getServiceName($version),[], null,5);
    }
    public function reloadPHP($version)
    {
        Screen::get()->executeCommand("systemctl reload ". (new PHP)->getServiceName($version),[], null,5);
    }
    public function rebuildOtherVhosts()
    {
        file_put_contents($this->othervhostsfiles,"IncludeOptional vhosts/*.conf");
    }
    public function removeDirectory($path) {
        system('rm -rf -- ' . escapeshellarg($path), $retval);
        return $retval == 0; // UNIX commands return zero on success
    }
}
