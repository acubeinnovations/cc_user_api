<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
    exit();
}


class User {

    private $connection;

    function __construct() {
        require_once dirname(__FILE__) . '/class_connection.php';
		$db = New Connection();
        $this->connection = $db->connect();
    }


    public function validateAppId($app_key) {
		return false;
    }

    public function getUserId($app_key) {
		return false;
    }
    public function checkLogin($mobile, $password) {
          $strSQL = "SELECT id FROM customers WHERE mobile = '".mysql_real_escape_string($mobile);
          $strSQL .= "' AND password='".md5($password)."'";
          $rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
          if ( mysql_num_rows($rsRES) ==1 ){
                return true;
          }
          else{
               return false;
          }
    }
    public function getUserByMobile($mobile) {
          $strSQL = "SELECT * FROM customers WHERE mobile = '".mysql_real_escape_string($mobile);
          $strSQL .= "'";
		  $user_array = array();
          $rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
          if ( mysql_num_rows($rsRES) == 1 ){
			   $user_array["id"] = mysql_result($rsRES,0,'id');
               $user_array["name"] = mysql_result($rsRES,0,'name');
               $user_array["mobile"] = mysql_result($rsRES,0,'mobile');
               $user_array["app_id"] = mysql_result($rsRES,0,'app_id');

                return $user_array;
          }
          else{
                $this->error_description = "Login Failed";
                return false;
          }
    }


}


?>
