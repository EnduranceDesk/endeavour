<?php

namespace App\Classes\Apache;

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
    protected $php74 = "/etc/opt/remi/php74/php-fpm.d";
    protected $sock = "/etc/endurance/configs/php/php74/";
    function addMainDomain($domain_without_www, $username)
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
        $virtualhost = view("templates.apache.NONSSL", ['domain_without_www'=> $domain_without_www, 'username' => $username])->render();
        $vhost_path = $this->vhostdir . DIRECTORY_SEPARATOR . "NONSSL_" . $domain_without_www . ".conf";
        if (file_exists($vhost_path)) {
            throw new \Exception("Domain already added to the server.", 1);
            return false;
        }
        file_put_contents($vhost_path, $virtualhost);

        $php74fpmconfig = view("templates.phpfpm.config", ['domain_without_www'=> $domain_without_www, 'username' => $username, 'apacheuser' => $this->apacheuser, 'apachegroup' => $this->apachegroup])->render();
        $phpfpm_path = $this->php74 . DIRECTORY_SEPARATOR . $domain_without_www . ".conf";
        if (file_exists($phpfpm_path)) {
            unlink($phpfpm_path);
        }
        
        file_put_contents($phpfpm_path, $php74fpmconfig);
        if (file_exists($phpfpm_path) & file_exists($vhost_path)) {
            $this->rebuildOtherVhosts();
            $this->reload();
            $this->reloadPHP74();
            sleep(2);
            return true;
        }
        unlink($vhost_path);
        unlink($phpfpm_path);
        // $this->reload();
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
    public function updateSSL(string $username,string  $domain,string  $chain)
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
        $virtualhost = view("templates.apache.SSL", ['domain_without_www'=> $domain, 'username' => $username, 'ssldir' => $domainSSLPath . DIRECTORY_SEPARATOR])->render();
        file_put_contents($vhost_path, $virtualhost);
        if (file_exists($vhost_path)) {
            $this->rebuildOtherVhosts();
            return true;
        }
        unlink($vhost_path);
        $this->reload();
        return false;
    }
    public function reload()
    {
        exec("systemctl reload httpd");
    }
    public function restartPHP74()
    {
        exec("systemctl restart php74-php-fpm");
    }
    public function reloadPHP74()
    {
        exec("systemctl reload php74-php-fpm");
    }
    public function rebuildOtherVhosts()
    {
        file_put_contents($this->othervhostsfiles,"IncludeOptional vhosts/*.conf");
    }
    public function removeDirectory($path) {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? removeDirectory($file) : unlink($file);
        }
        rmdir($path);
        return;
    }
}