<?php define('CHECK_INCLUDED', true);

require_once 'include/conf.php';
require_once 'include/functions.php';
require 'include/libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

//Global Variables
$user_id = NULL;


//authenticate_user usage
// $app->get('/ACTION', 'authenticate_user', function() use ($app) {
// }
//
// OR
//
// $app->post('/ACTION', 'authenticate_user', function() use ($app) {
// }
//


function authenticate_user(\Slim\Route $route) {
    $response = array();
    $app = \Slim\Slim::getInstance();
    // get request headers
    $headers = apache_request_headers();

	require_once dirname(__FILE__) . '/include/class/class_user.php';
	$app_user = New User();

    // Verifying APP ID IN Header
    if (isset($headers['APP_ID'])) {
        $app_id = $headers['APP_ID'];
        // Check APP ID in DB
		if ($app_user->validateAppId($app_id)) {
			//set Global User ID
			global $user_id;
            $user_id = $app_user->getUserId($api_id);
        }else{
            // Invalid APP ID (not found in DB)
            $response["error"] = true;
            $response["message"] = "Invalid APP ID";
            ReturnResponse(401, $response);
            $app->stop();           
        }
    }else{
        // missing APP ID in header
        $response["error"] = true;
        $response["message"] = "APP ID is misssing";
        ReturnResponse(400, $response);
        $app->stop();
    }
}




/**
 * validate-token
 * url - /validate-token
 * method - POST
 * params - action,token,app_id,IMEI
 */

$app->post('/validate-token', function() use ($app) {
	// check for required param, if required
	verifyRequiredParams(array('action', 'token','app_id','IMEI'));

	// read post params, if required
	$action = $app->request()->post('action');
	$token = md5($app->request()->post('token'));
	$app_id = $app->request()->post('app_id');
	$IMEI = $app->request()->post('IMEI');


	// define response array 
	$response = array();


	//add your class, if required
	require_once dirname(__FILE__) . '/include/class/class_customer.php';
	$customer = New Customer();
	$validate = $customer->validate_token($token,$app_id,$IMEI);


	//please replace $validate ans $user_data with your variables
	//$validate = false;
	$user_data = array();

	if ($validate) {
		$user_data['id'] = $validate['id'];
		$user_data['name'] = $validate['name'];
		$user_data['token'] = $validate['token'];

		$response["action"] = "validate-token";
		$response["error"] = 0;
		$response["success"] = 1;
		$response['error_message'] = "";
		$response['user_data'] = $user_data;
	} else {
	//  error occurred
		$response["action"] = "validate-token";
		$response["error"] = 1;
		$response["success"] = 0;
		$response['error_message'] = "Invalid token identified";
	}

	ReturnResponse(200, $response);
});


/**
 * forget_password
 * url - /forget_password
 * method - POST
 * params - action,mobile,app_id,IMEI
 */

$app->post('/forget_password', function() use ($app) {
	// check for required param, if required
	verifyRequiredParams(array('action', 'mobile','app_id','IMEI'));

	// read post params, if required
	$action = $app->request()->post('action');
	$mobile = md5($app->request()->post('mobile'));
	$app_id = $app->request()->post('app_id');
	$IMEI = $app->request()->post('IMEI');
});

/**
 * sign_up
 * url - /sign_up
 * method - POST
 * params - action,email,mobile,name,app_id,IMEI
 */

$app->post('/sign_up', function() use ($app) {

});


/**
 * login
 * url - /login
 * method - POST
 * params - action,mobile,password,app_id,IMEI
 */

$app->post('/login', function() use ($app) {

});

/**
 * booking
 * url - /booking
 * method - POST
 * params - action,from,to,mobile,date,time,priority,app_id,IMEI,token
 */

$app->post('/booking', function() use ($app) {

});

/**
 * booking_list
 * url - /booking_list
 * method - POST
 * params - action,app_id,IMEI,token
 */

$app->post('/booking_list', function() use ($app) {

});












/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        ReturnResponse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email 
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email is not valid';
        ReturnResponse(400, $response);
        $app->stop();
    }
}



















function ReturnResponse($http_response, $response) {
	//return response : json
    $app = \Slim\Slim::getInstance();
    $app->status($http_response);
    $app->contentType('application/json');
    echo json_encode($response);
}

$app->run();
?>
