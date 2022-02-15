<?php

namespace App\Classes\LinuxUser;

use App\Helpers\Screen;

/**
 *  Linux user authentication class
 */
class LinuxUser
{
    public static function validateUsername($username)
    {
        $shadow =  preg_split("/[$:]/",`cat /etc/shadow | grep "^$username\:"`);
        if ($shadow[0] == $username) {
            return true;
        }
        return false;
    }
    public static function validate($username, $password)
    {
        $password = escapeshellarg($password);
        $shadow =  preg_split("/[$:]/",`cat /etc/shadow | grep "^$username\:"`);
        $makepassword = preg_split("/[$:]/",trim(`openssl passwd -6 -salt $shadow[3] $password`));
        if($shadow[4] == $makepassword[3]) {
            return true;
        }
        return false;
    }
    public static function currentUser()
    {
        $userInfo = posix_getpwuid(posix_getuid());
        $user = $userInfo['name'];
        $groupInfo = posix_getgrgid(posix_getgid());
        $group = $groupInfo = $groupInfo['name'];
        return ($user . ":" .  $group);
    }
    public static function add($username, $password)
    {
        if (LinuxUser::validateUsername($username)) {
            throw new \Exception("User already exist", 1);
            return false;
        }
        $process = Screen::get()->executeFileNow(base_path("shell_scripts/create_new_user.shell"), [$username, $password], null, 10);
        return $process->success;
    }
    public static function remove($username)
    {
        $process = Screen::get()->executeFileNow(base_path("shell_scripts/delete_user.shell"), [$username], null, 10);
        if (LinuxUser::validateUsername($username)) {
            return false;
        }
        return $process->success;
    }
}
