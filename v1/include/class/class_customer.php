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
		$token = $this->createToken($user_data['mobile']);
		$password = $this->createPassword();
		if($token){
			//new customer
			$strSQL = "INSERT INTO customers(name,email,mobile,app_id,imei,token,password,customer_type_id,registration_type_id,organisation_id) VALUES(";
			$strSQL .= "'".mysql_real_escape_string($user_data['name']);
			$strSQL .= "','".mysql_real_escape_string($user_data['email']);
			$strSQL .= "','".mysql_real_escape_string($user_data['mobile']);
			$strSQL .= "','".mysql_real_escape_string($user_data['app_id']);
			$strSQL .= "','".mysql_real_escape_string($user_data['IMEI']);
			$strSQL .= "','".mysql_real_escape_string($token);
			$strSQL .= "','".mysql_real_escape_string(md5($password));
			$strSQL .= "','".mysql_real_escape_string(WALKIN_CUSTOMER);
			$strSQL .= "','".mysql_real_escape_string(APP_REGISTRATION);
			$strSQL .= "','".mysql_real_escape_string(ORG_CNC);
			$strSQL .= "')";
	
			$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
			if(mysql_affected_rows($this->connection) == 1){
				$this->error_description = "Registration success password sent through sms";
				return array(mysql_insert_id(),$user_data['mobile'],$password);
			}else{
				$this->error_description = "Customer not added";
				return false;
			}
		}else{
			$this->error_description = "Invalid Token";
			return false;
		}	
		
	}

	
	//create token with mobile number and time md5 format
	public function createToken($mobile = '')
	{
		if($mobile){
			$str = $mobile.strtotime(date("Y-m-d h:i:s"));
			return md5($str);
		}else{
			return false;
		}
	
	}
	
	//create random  6 char password
	public function createPassword()
	{
		
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
   		$randomString = '';
    		for ($i = 0; $i < 6; $i++) {
        		$randomString .= $characters[rand(0, strlen($characters) - 1)];
    		}
    		return $randomString;
	}
	



	public function validateAppId($app_key) {
		return false;
	}

	public function getUserId($app_key) {
		return false;
	}
	
	//user login check
	public function checkLogin($mobile, $password,$app_id,$IMEI) {

		$strSQL = "SELECT id,name FROM customers WHERE mobile = '".mysql_real_escape_string($mobile);
		$strSQL .= "' AND password='".mysql_real_escape_string(md5($password));
		$strSQL .= "' AND app_id ='".mysql_real_escape_string($app_id);
		$strSQL .= "' AND imei ='".mysql_real_escape_string($IMEI)."'";
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if ( mysql_num_rows($rsRES) ==1 ){
			$token = $this->createToken($mobile);
			if($token){
				$userdata = array('id'=>mysql_result($rsRES,0,'id'),
					'name' => mysql_result($rsRES,0,'name'),
					'token' => $token
					);
				$update_token = "UPDATE customers SET token = '".mysql_real_escape_string($token)."' WHERE id = '".mysql_real_escape_string($userdata['id'])."'";
				$rsRES = mysql_query($update_token,$this->connection) or die(mysql_error(). $update_token );
				return $userdata;//token generated
			}else{
				return false;//token not generated
			}
				
		}
		else{
			return false;//user not found
		}
	}

	//user account exists
	public function checkMobileAccountExists($mobile,$app_id,$IMEI) {

		$strSQL = "SELECT id FROM customers WHERE mobile = '".mysql_real_escape_string($mobile);
		$strSQL .= "' AND app_id ='".$app_id."";
		$strSQL .= "' AND imei ='".$IMEI."'";
		
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if ( mysql_num_rows($rsRES) ==1 ){
			$password = $this->createPassword();
			if($password){
				return $password;//password generated
			}else{
				return false;//password not generated
			}
				
		}
		else{
			return false;//user not found
		}
	}

	//reset password with appid ,imei and mobile
	public function reset_password($mobile,$app_id,$IMEI,$new_password)
	{
		$strSQL = "UPDATE customers SET password = '".mysql_real_escape_string(md5($new_password))."'";
		$strSQL .= "WHERE mobile = '".mysql_real_escape_string($mobile);
		$strSQL .= "' AND app_id ='".mysql_real_escape_string($app_id)."";
		$strSQL .= "' AND imei ='".mysql_real_escape_string($IMEI)."'";
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if(mysql_affected_rows($this->connection) == 1){
			return true;
		}else{
			return false;
		}
	}

	public function getUserByMobile($mobile) {
		$strSQL = "SELECT * FROM customers WHERE mobile = '".mysql_real_escape_string($mobile)."'";

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

	public function getUserById($id) {
		$strSQL = "SELECT * FROM customers WHERE id = '".mysql_real_escape_string($id)."'";

		$user_array = array();
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if ( mysql_num_rows($rsRES) == 1 ){
			$user_array["id"] = mysql_result($rsRES,0,'id');
			$user_array["name"] = mysql_result($rsRES,0,'name');
			$user_array["mobile"] = mysql_result($rsRES,0,'mobile');
			$user_array["app_id"] = mysql_result($rsRES,0,'app_id');
			$user_array["password"] = mysql_result($rsRES,0,'password');

			return $user_array;
		}
		else{
			$this->error_description = "Invalid user";
			return false;
		}
	}



	//validate token
	public function validate_token($token,$app_id,$IMEI)
	{
		$strSQL = "SELECT * FROM customers WHERE token = '".mysql_real_escape_string($token);
		$strSQL .= "' AND app_id = '".mysql_real_escape_string($app_id);
		$strSQL .= "' AND imei = '".mysql_real_escape_string($IMEI);
		$strSQL .= "'";

		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if ( mysql_num_rows($rsRES) == 1 ){
			return mysql_fetch_assoc($rsRES);
		}
		else{
			return false;
		}
	}


	public function getId($username, $password) {

		$strSQL = "SELECT id FROM customers WHERE mobile = '".mysql_real_escape_string($username);
		$strSQL .= "' AND password='".mysql_real_escape_string(md5($password))."'";
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if ( mysql_num_rows($rsRES) ==1 ){
			return mysql_result($rsRES,0,'id');
		}
		else{
			return false;//user not found
		}
	}


}


?>
