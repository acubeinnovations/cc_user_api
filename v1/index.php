<?php define('CHECK_INCLUDED', true);

require_once 'include/conf.php';
require_once 'include/functions.php';
require 'include/libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();


//Global Variables
$user_id = NULL;

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
 * User Login
 * url - /login
 * method - POST
 * params - mobile, password
 */
$app->post('/login', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('mobile', 'password'));

    // reading post params
    $mobile = $app->request()->post('mobile');
    $password = $app->request()->post('password');
    $response = array();

	require_once dirname(__FILE__) . '/include/class/class_user.php';
	$app_user = New User();

    // check for correct mobile and password
    if ($app_user->checkLogin($mobile, $password)) {
        // get the user by mobile
        $user = $app_user->getUserByMobile($mobile);

        if ($user != NULL) {
            $response["error"] = false;
            $response['name'] = $user['name'];
            $response['mobile'] = $user['mobile'];
            $response['app_id'] = $user['app_id'];
        } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
        }
    } else {
        // user credentials are wrong
        $response['error'] = true;
        $response['message'] = 'Login failed. Incorrect credentials';
    }

    ReturnResponse(200, $response);
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
        echoRespnse(400, $response);
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
        echoRespnse(400, $response);
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
