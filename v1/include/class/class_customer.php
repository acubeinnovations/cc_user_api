<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
	exit();
}


class Customer {

	private $connection;
	public  $error_description;

	function __construct() {
		require_once dirname(__FILE__) . '/class_connection.php';
		$db = New Connection();
		$this->connection = $db->connect();
	}
	
	public function  sign_up($user_data = array())
	{
		//new customer
		$strSQL = "INSERT INTO customers(name,email,mobile,app_id,IMEI) VALUES(";
		$strSQL .= "'".mysql_real_escape_string($user_data['name']);
		$strSQL .= "'".mysql_real_escape_string($user_data['email']);
		$strSQL .= "'".mysql_real_escape_string($user_data['mobile']);
		$strSQL .= "'".mysql_real_escape_string($user_data['app_id']);
		$strSQL .= "'".mysql_real_escape_string($user_data['IMEI']);
		$strSQL .= "')";
	
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if(mysql_affected_rows($this->connection) == 1){
			$this->error_description = "Registration success password sent through sms";
			return mysql_insert_id();
		}else{
			$this->error_description = "Customer not added";
			return false;
		}
			
		
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



	//validate token
	public function validate_token($token,$app_id,$IMEI)
	{
		$strSQL = "SELECT * FROM customers WHERE token = '".mysql_real_escape_string($token);
		$strSQL .= "' AND app_id = '".mysql_real_escape_string($app_id);
		$strSQL .= "' AND IMEI = '".mysql_real_escape_string($IMEI)."'";
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if ( mysql_num_rows($rsRES) == 1 ){
			return mysql_fetch_assoc($rsRES);
		}
		else{
			return false;
		}
	}


}


?>
