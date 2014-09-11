<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
    exit();
}

class Sms {

    var $mobile = "";
	var $message = ""; 
	var $credit ="";
	var $amount ="";
	var $error= false;
    var $error_number= gINVALID;
    var $error_description= "";



    function __construct()
    {

    }

function user_account_activation_sms($username ,$password ){

	   $message="Thank You for registering with mydailytest.com. Please login with the username:".$username." and password:".$password."  Login Url : ".WEB_URL."/login.php";
	$this->message=$message;


		$url="http://tx.ebensms.in/api/web2sms.php?workingkey=9412x5966i644f695452&sender=MDTEST&to=".$this->mobile."&message=".urlencode($this->message);

		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output=curl_exec($ch);
		curl_close($ch);                                
		return $output;

	}

function user_password_reset_sms($password){

	   $message="Thank you for contacting mydailytest.com. Please login with your username : ".$this->mobile." and new password : ".$password.". to take the tests.";
	$this->message=$message; 


		$url="http://tx.ebensms.in/api/web2sms.php?workingkey=9412x5966i644f695452&sender=MDTEST&to=".$this->mobile."&message=".urlencode($this->message);

		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output=curl_exec($ch);
		curl_close($ch);                                
		return $output;

	}

function user_welcome_sms_unpaid($phone){
$message="Thank You for registering with mydailytest.com. Please login to mydailytest.com and recharge your account to take the tests.";
	$this->message=$message;


		$url="http://tx.ebensms.in/api/web2sms.php?workingkey=9412x5966i644f695452&sender=MDTEST&to=".$phone."&message=".urlencode($this->message);

		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output=curl_exec($ch);
		curl_close($ch);                                
		return $output;

}


function user_welcome_sms_paid($phone){
$message="Thank You for registering with mydailytest.com. Your account has been credited with Rs ".$this->amount.". Please login to mydailytest.com to take the tests.";
	$this->message=$message;


		$url="http://tx.ebensms.in/api/web2sms.php?workingkey=9412x5966i644f695452&sender=MDTEST&to=".$phone."&message=".urlencode($this->message);

		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output=curl_exec($ch);
		curl_close($ch);                                
		return $output;

}

function user_exam_preference_update_sms(){

$message="Thank You for the update with mydailytest.com. Please login to mydailytest.com and take tests as per the new preferences.";
	$this->message=$message;


		$url="http://tx.ebensms.in/api/web2sms.php?workingkey=9412x5966i644f695452&sender=MDTEST&to=".$this->mobile."&message=".urlencode($this->message);

		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output=curl_exec($ch);
		curl_close($ch);                                
		return $output;
	
}


    
}
?>
