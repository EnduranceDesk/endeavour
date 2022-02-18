<?php

namespace App\Classes\MySQL;

use Illuminate\Support\Facades\Log;

/**
 * MySQL
 */
class MySQL
{
    private  $db = null;
    function __construct($host, $username, $password, $database)
    {
        $db = new \mysqli($host, $username, $password, $database);
        if ($db->connect_errno) die($db->connect_error);
        $this->db = $db;
        return true;
    }
    public function createUserSet($username, $password)
    {
        $db = $username . "_default";
        $check = $this->createDatabase($db);

        if (!$check) {
            throw new \Exception("Error Processing Request: Cannot create the DB", 1);

            return false;
        }
        $check = $this->createUser($username, $password);

        if (!$check) {
            $this->dropDatabase($db);
            throw new \Exception("Error Processing Request: Cannot create the DB user", 1);
            return false;
        }
        $check = $this->linkDBToUser($db, $username);
        if (!$check) {
            $this->dropDatabase($db);
            $this->dropUser($username);
            throw new \Exception("Error Processing Request: Cannot link the DB user linkage", 1);
            return false;
        }
        return true;
    }
    public function removeUserSet($username)
    {
        $db = $username . "_default";
        $check = $this->dropDatabase($db);
        $check = $this->dropUser($username);
        return $check;
    }
    public  function createDatabase ($name) {
        if (!$this->db) return false;
        return $this->db->query(" CREATE DATABASE `{$name}` ");
    }
    public  function dropDatabase ($name) {
        if (!$this->db) return false;
        return $this->db->query(" DROP DATABASE `{$name}` ");
    }
    public  function createUser($name, $password) {
        if (!$this->db) return false;
        $queryForUserCreation = " CREATE USER '{$name}'@'%' IDENTIFIED WITH mysql_native_password BY '{$password}'; ";
        $this->db->query($queryForUserCreation);
        return $this->db->query(" ALTER USER '{$name}'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0; ");
    }
    public  function changeNonRootUserPassword($name, $password) {
        if (!$this->db) return false;
        $c= $this->db->query("ALTER USER '{$name}'@'%' IDENTIFIED BY '{$password}';");
        $this->flushPrivileges();
        return $c;
    }
    public  function dropUser($username) {
        if (!$this->db) return false;
        return $this->db->query(" DROP USER '{$username}'; ");
    }
    public  function flushPrivileges() {
        if (!$this->db) return false;
        return $this->db->query("FLUSH PRIVILEGES;");
    }
    public function linkDBToUser($db, $user)
    {
        if (!$this->db) return false;
        return $this->db->query(" GRANT ALL PRIVILEGES ON `{$db}`.* TO '$user'@'%'; ");
    }
    public  function query ($query) {
        if (!$this->db) return false;
        return $this->db->query($query);;
    }

}
