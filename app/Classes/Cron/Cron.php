<?php


namespace App\Classes\Cron;

use App\Helpers\Screen;

/**
 * Cron Job
 */
class Cron
{

    public static function  add($user, $command) {
        $process = Screen::get()->executeFileNow(base_path("shell_scripts/add_cron_entry.shell"), [$user  ,$command], null, 15);
        return $process;
    }
    public static function  delete($user, $command) {

        $text = Cron:: fetch($user) ;
        if ($text) {
            $text=str_replace($command, "", $text);
            Cron::create($user, $text);
        }
        return Cron::rebuild($user);
    }
    public static function  rebuild($user) {

        $process = Screen::get()->executeFileNow(base_path("shell_scripts/rebuild_cron_entries.shell"), [$user  ,$command], null, 15);
        return $process;
    }
    public static function create($user, $text) {
        return file_put_contents("/etc/endurance/configs/cron/$user/crontab", $text);
    }
    public static function  fetch($user) {
        if (!file_exists("/etc/endurance/configs/cron/$user/crontab")) {
            return false;
        }
        return file_get_contents("/etc/endurance/configs/cron/$user/crontab");
    }
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
