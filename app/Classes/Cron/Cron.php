<?php


namespace App\Classes\Cron;

/**
 * Cron Job
 */
class Cron
{
    public function isUserAllowed(string $username)
    {
        $cronAllow =  preg_split("/[$:]/",`cat /etc/cron.allow | grep $username`);
        if (trim($cronAllow[0]) == $username) {
            return true;
        }
        return false;
    }    
    public function allowUser(string $username)
    {
        if ($this->isUserAllowed($username)) {
            return true;
        }
        shell_exec('echo "' . $username .'" >> /etc/cron.allow');
        if ($this->isUserAllowed($username)) {
            return true;
        }
        return false;

    }
    public function disallowUser(string $username)
    {
        if (!$this->isUserAllowed($username)) {
            return true;
        }
        shell_exec("sed -i 's/{$username}//g' /etc/cron.allow");
        if (!$this->isUserAllowed($username)) {
            return true;
        }
        return false;
    }
}