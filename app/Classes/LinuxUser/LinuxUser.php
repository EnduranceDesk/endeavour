<?php

namespace App\Classes\LinuxUser;

/**
 * Linux User Authentication
 */
class LinuxUser
{
    public static function validate($username, $password)
    {
        $shadow =  preg_split("/[$:]/",`cat /etc/shadow | grep "^$username\:"`);
        // var_dump($shadow);
        // use mkpasswd command to generate shadow line passing $pass and $shadow[3] (salt)
        // split the result into component parts
        // $makepassword = preg_split("/[$:]/",trim(`mkpasswd -m sha-512 $pass $shadow[3]`));
        $makepassword = preg_split("/[$:]/",trim(`openssl passwd -6 -salt $shadow[3] $password`));
        // var_dump($makepassword);
        // compare the shadow file hashed password with generated hashed password and return
        // return ($shadow[4] == $makepassword[3]);
        if($shadow[4] == $makepassword[3])
        {
            return true;
        } else {
            return false;
        }
    }
    
}