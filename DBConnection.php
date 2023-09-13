<?php
date_default_timezone_set('Asia/Manila');
if(!is_dir(__DIR__.'./db'))
    mkdir(__DIR__.'./db');
if(!defined('host')) define('host','localhost');
if(!defined('username')) define('username','root');
if(!defined('password')) define('password','');
if(!defined('database')) define('database','messaging_db');

Class DBConnection {
    public $conn;
    function __construct(){
        $this->conn = new mysqli(host,username,password,database);
        if(!$this->conn){
            die("Database Connection Failed. Error: ".$this->conn->error);
        }

    }
    function isMobileDevice(){
        $aMobileUA = array(
            '/iphone/i' => 'iPhone', 
            '/ipod/i' => 'iPod', 
            '/ipad/i' => 'iPad', 
            '/android/i' => 'Android', 
            '/blackberry/i' => 'BlackBerry', 
            '/webos/i' => 'Mobile'
        );
    
        //Return true if Mobile User Agent is detected
        foreach($aMobileUA as $sMobileKey => $sMobileOS){
            if(preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])){
                return true;
            }
        }
        //Otherwise return false..  
        return false;
    }
    function __destruct(){
         $this->conn->close();
    }
}

$mydb = new DBConnection();
$conn = $mydb->conn;