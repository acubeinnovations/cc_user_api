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
	$token = $app->request()->post('token');
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

		$response["action"] = $action;
		$response["error"] = 0;
		$response["success"] = 1;
		$response['error_message'] = "";
		$response['user_data'] = $user_data;
	} else {
	//  error occurred
		$response["action"] = $action;
		$response["error"] = 1;
		$response["success"] = 0;
		$response['error_message'] = "Invalid token identified";
	}

	ReturnResponse(200, $response);
});


/**
 * sign-up
 * url - /sign-up
 * method - POST
 * params - action,email,mobile,name,app_id,IMEI
 */

$app->post('/sign-up', function() use ($app) {
	// check for required param, if required
	verifyRequiredParams(array('action','email', 'mobile','name','app_id','IMEI'));
	
	require_once dirname(__FILE__) . '/include/class/class_customer.php';
	$customer = new Customer();

	// define response array 
	$response = array();

	// read post params, if required
	$user_data = array();
	$action = $app->request()->post('action');
	$user_data['email'] = $app->request()->post('email');
	$user_data['mobile'] = $app->request()->post('mobile');
	$user_data['name'] = $app->request()->post('name');
	$user_data['app_id'] = $app->request()->post('app_id');
	$user_data['IMEI'] = $app->request()->post('IMEI');

	if($customer->getUserByMobile($user_data['mobile'])){//customer exists
		$response["action"] = $action;
		$response["error"] = 1;
		$response["success"] = 0;
		$response['error_message'] = "Already registered with this mobile number";
	}else{
	
		list($new_id,$username,$password) = $customer->sign_up($user_data);
		$customer_id = $customer->getId($username,$password);
		if($customer_id){
			$customer->add_fa_customer($customer_id);
			//account info sms
			require_once dirname(__FILE__) . '/include/class/class_sms.php';
			$sms = new Sms();
			$message = "Thankyou for registering  with Connect n Cabs. Your username is '".$username."' and password is '".$password."'. Enjoy our Service.";
			//$sms->send_sms($cust_details['mobile'] ,$message);

			$response["action"] = $action;
			$response["error"] = 0;
			$response["success"] = 1;
			$response['error_message'] = "";
			$response['success_message'] = $message;//"Registration success password sent through sms";
		}else{
			$response["action"] = $action;
			$response["error"] = 1;
			$response["success"] = 0;
			$response['error_message'] = "Registration failed";
		}

		
	}
	ReturnResponse(200, $response);
});


/**
 * login
 * url - /login
 * method - POST
 * params - action,mobile,password,app_id,IMEI
 */

$app->post('/login', function() use ($app) {
	// check for required param, if required
	verifyRequiredParams(array('action','mobile','password','app_id','IMEI'));
	
	require_once dirname(__FILE__) . '/include/class/class_customer.php';
	$customer = new Customer();
	$user_data = array();

	// define response array 
	$response = array();

	// read post params, if required
	$action = $app->request()->post('action');
	$mobile = $app->request()->post('mobile');
	$password = $app->request()->post('password');
	$app_id = $app->request()->post('app_id');
	$IMEI = $app->request()->post('IMEI');

	$user_data = $customer->checkLogin($mobile, $password,$app_id,$IMEI);
	if($user_data){
		$response["action"] = $action;
		$response["error"] = 0;
		$response["success"] = 1;
		$response['error_message'] = "";
		$response['user_data'] = $user_data;	
	}else{
		$response["action"] = $action;
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

$app->post('/forget-password', function() use ($app) {
	// check for required param, if required
	verifyRequiredParams(array('action', 'mobile','app_id','IMEI'));

	// read post params, if required
	$action = $app->request()->post('action');
	$mobile = $app->request()->post('mobile');
	$app_id = $app->request()->post('app_id');
	$IMEI = $app->request()->post('IMEI');

	require_once dirname(__FILE__) . '/include/class/class_customer.php';
	$customer = new Customer();

	// define response array 
	$response = array();

	$new_password = $customer->checkMobileAccountExists($mobile,$app_id,$IMEI);
	$reset_password_check = $customer->reset_password($mobile,$app_id,$IMEI,$new_password);

	if($reset_password_check){

		//password sms
		require_once dirname(__FILE__) . '/include/class/class_sms.php';
		$sms = new Sms();
		$message = "Thankyou for interest with Connect n Cabs. Your one time password is '“.$new_password.”'.  Enjoy our Service.";
		//$sms->send_sms($cust_details['mobile'] ,$message);

		$response["action"] = $action;
		$response["error"] = 0;
		$response["success"] = 1;
		$response['error_message'] = "";
		$response['success_message'] = $message;//"New password sent through sms";	

	}else{
		$response["action"] = $action;
		$response["error"] = 1;
		$response["success"] = 0;
		$response['error_message'] = "No account registered with this mobile";
	}
	
	
	ReturnResponse(200, $response);
});


/**
 * booking
 * url - /booking
 * method - POST
 * params - action,from,to,mobile,date,time,priority,app_id,IMEI,token
 */

$app->post('/booking', function() use ($app) {
	// check for required param, if required
	//verifyRequiredParams(array('action','from','to','mobile','date','time','priority','app_id','IMEI','token','vehicle_type_id','trip_model_id'));
echo "hi";exit;
	require_once dirname(__FILE__) . '/include/class/class_customer.php';
	$customer = new Customer();

	// define response array
	$trip_data = array();
	$response = array();
	$action = $app->request()->post('action');
	$token = $app->request()->post('token');
	$app_id = $app->request()->post('app_id');
	$IMEI  = $app->request()->post('IMEI');
	$vehicle_type_id = $app->request()->post('vehicle_type_id');
	$trip_model_id	= $app->request()->post('trip_model_id');

	$user_detail = $customer->validate_token($token,$app_id,$IMEI);



	
	if($user_detail){
		require_once dirname(__FILE__) . '/include/class/class_trip.php';
		$trip = new Trip();

	
		// read post params
		$action = $app->request()->post('action');
		$dataArray = array(
				'booking_date'  => date('Y-m-d'),
				'booking_time' => date('h:i:s'),
				'pick_up_date'  => date('Y-m-d',strtotime($app->request()->post('date'))),
				'pick_up_time' => date('h:i:s',strtotime($app->request()->post('time'))),
				'priority'  => $app->request()->post('priority'),
				'customer_id' => $user_detail['id'],
				'customer_type_id'  => $app->request()->post('customer_type_id'),
				'vehicle_type_id' => $vehicle_type_id,
				'trip_model_id' => $trip_model_id,
				'trip_status_id' => TRIP_STATUS_PENDING,
				'booking_source_id' => BOOKING_SOURCE_APP,
				'organisation_id' => ORG_CNC
				);

		$from = $app->request()->post('from');
		
		$to = $app->request()->post('to');
		$dataArray['pick_up_city'] = $from['city'];
		$dataArray['pick_up_area'] = $from['area'];
		$dataArray['pick_up_landmark'] = $from['landmark'];
		$dataArray['pick_up_lat'] = $from['lat'];
		$dataArray['pick_up_lng'] = $from['long'];

		$dataArray['drop_city'] = $to['city'];
		$dataArray['drop_area'] = $to['area'];
		$dataArray['drop_landmark'] = $to['landmark'];
		$dataArray['drop_lat'] = $to['lat'];
		$dataArray['drop_lng'] = $to['long'];
	
		 $trip_id = $trip->booking($dataArray);

		if($trip_id){
	
			$trip_detail = $trip->booking_details($trip_id);

			$trip_data['name'] = $user_detail['name'];
			$trip_data['mobile'] = $user_detail['mobile'];
			$trip_data['from'] = array(
						'city'=>$trip_detail['pick_up_city'],
						'area'=>$trip_detail['pick_up_area'],
						'lankmark'=>$trip_detail['pick_up_landmark'],		
						);
			$trip_data['to'] = array(
						'city'=>$trip_detail['drop_city'],
						'area'=>$trip_detail['drop_area'],
						'lankmark'=>$trip_detail['drop_landmark'],		
						);
			$trip_data['trip_date'] = date('d-M-y h:i a',strtotime($trip_detail['pick_up_date']." ".$trip_detail['pick_up_time']));
			$trip_data['date'] = strtotime($trip_detail['booking_date']." ".$trip_detail['booking_time']);
			$trip_data['confirmation'] = "1";

			$response["action"] = $action;
			$response["error"] = 0;
			$response["success"] = 1;
			$response["booking_details"] = $trip_data;	
		}else{
			$response["action"] = $action;
			$response["error"] = 1;
			$response["success"] = 0;
			$response["message"] = "unexpected error occured try later";
		}
	}else{
		$response["action"] = $action;
		$response["error"] = 1;
		$response["success"] = 0;
		$response["message"] = "Invalid Token";
	}
	ReturnResponse(200, $response);
	
});

/**
 * list-booking
 * url - /list-booking
 * method - POST
 * params - action,app_id,IMEI,token
 */

$app->post('/list-booking', function() use ($app) {
	// check for required param, if required
	verifyRequiredParams(array('action','app_id','IMEI','token'));

	require_once dirname(__FILE__) . '/include/class/class_trip.php';
	$trip = new Trip();

	// define response array
	$response = array();
	$action = $app->request()->post('action');
	$token = $app->request()->post('token');
	$app_id = $app->request()->post('app_id');
	$IMEI  = $app->request()->post('IMEI');
	
	$booing_details = $trip->get_booking_details_by_customer($app_id,$IMEI,$token);
	
	if($booing_details){
		$response["action"] = $action;
		$response["error"] = 0;
		$response["success"] = 1;
		$response["booking_details"] = $booing_details;
		
	}else{
		$response["action"] = $action;
		$response["error"] = 1;
		$response["success"] = 0;
		$response["message"] = "no booking details avilable";
	}

	ReturnResponse(200, $response);
	
});

/**
 * locate-taxi
 * url - /locate-taxi
 * method - POST
 * params - action,app_id,IMEI,token,booking_id
 */

$app->post('/locate-taxi', function() use ($app) {
	// check for required param, if required
	verifyRequiredParams(array('action','app_id','IMEI','token','booking_id'));

	require_once dirname(__FILE__) . '/include/class/class_customer.php';
	$customer = new Customer();

	require_once dirname(__FILE__) . '/include/class/class_vehicle_location_log.php';
	$taxi_loc = new VehicleLocationLog();

	// define response array
	$response = array();
	
	//read params
	$action = $app->request()->post('action');
	$token = $app->request()->post('token');
	$app_id = $app->request()->post('app_id');
	$IMEI  = $app->request()->post('IMEI');
	$booking_id  = $app->request()->post('booking_id');

	$user_detail = $customer->validate_token($token,$app_id,$IMEI);

	if($user_detail){
	
		//get trip location log latest
		$taxi_loc_detail = $taxi_loc->locate_taxi($booking_id);
		if($taxi_loc_detail){
			$response["action"] = $action;
			$response["error"] = 0;
			$response["success"] = 1;
			$response["lat"] = $taxi_loc_detail['lat'];
			$response["long"] = $taxi_loc_detail['lng'];
		}else{
			$response["action"] = $action;
			$response["error"] = 1;
			$response["success"] = 0;
			$response["message"] = "unexpected error occured try later";
		}
	}else{
		$response["action"] = $action;
		$response["error"] = 1;
		$response["success"] = 0;
		$response["message"] = "Invalid Token";
	}

	ReturnResponse(200, $response);
});

/**
 * single-booking
 * url - /single-booking
 * method - POST
 * params - action,app_id,IMEI,token,booking_id
 */

$app->post('/single-booking', function() use ($app) {
	// check for required param, if required
	verifyRequiredParams(array('action','app_id','IMEI','token','booking_id'));

	require_once dirname(__FILE__) . '/include/class/class_customer.php';
	$customer = new Customer();

	require_once dirname(__FILE__) . '/include/class/class_trip.php';
	$trip = new Trip();

	// define response array
	$response = array();
	
	//read params
	$action = $app->request()->post('action');
	$token = $app->request()->post('token');
	$app_id = $app->request()->post('app_id');
	$IMEI  = $app->request()->post('IMEI');
	$booking_id  = $app->request()->post('booking_id');

	$user_detail = $customer->validate_token($token,$app_id,$IMEI);

	if($user_detail){
	
		//get trip location log latest
		$trip_detail = $trip->booking_details($booking_id);
		if($trip_detail){

			$trip_data['name'] = $user_detail['name'];
			$trip_data['mobile'] = $user_detail['mobile'];
			$trip_data['from'] = array(
						'city'=>$trip_detail['pick_up_city'],
						'area'=>$trip_detail['pick_up_area'],
						'lankmark'=>$trip_detail['pick_up_landmark'],		
						);
			$trip_data['to'] = array(
						'city'=>$trip_detail['drop_city'],
						'area'=>$trip_detail['drop_area'],
						'lankmark'=>$trip_detail['drop_landmark'],		
						);
			$trip_data['trip_date'] = date('d-M-y h:i a',strtotime($trip_detail['pick_up_date']." ".$trip_detail['pick_up_time']));
			$trip_data['date'] = strtotime($trip_detail['booking_date']." ".$trip_detail['booking_time']);
			$trip_data['confirmation'] = "1";

			$response["action"] = $action;
			$response["error"] = 0;
			$response["success"] = 1;
			$response["booking_details"] = $trip_data;
		}else{
			$response["action"] = $action;
			$response["error"] = 1;
			$response["success"] = 0;
			$response["message"] = "unexpected error occured try later";
		}
	}else{
		$response["action"] = $action;
		$response["error"] = 1;
		$response["success"] = 0;
		$response["message"] = "Invalid Token";
	}

	ReturnResponse(200, $response);
});



/**
 * list-vehicle-types.json
 * url - /vehicle-types.json
 * method - POST
 */

$app->get('/vehicle-types.json', function() use ($app) {

	require_once dirname(__FILE__) . '/include/class/class_list.php';
	$list = new Listing();

	// define response array
	$response = array();

	$vehicle_types = $list->vehicle_types();
	if($vehicle_types){
		$response["error"] = 0;
		$response["success"] = 1;
		$response["vehicle_types"] = $vehicle_types;
	}else{
		$response["error"] = 1;
		$response["success"] = 0;
		$response["message"] = "no vehicle types avilable";
	}

	ReturnResponse(200, $response);

});

/**
 * list-trip-models.json
 * url - /trip-models.json
 * method - POST
 */

$app->get('/trip-models.json', function() use ($app) {

	require_once dirname(__FILE__) . '/include/class/class_list.php';
	$list = new Listing();

	// define response array
	$response = array();

	$trip_models = $list->trip_models();

	if($trip_models){
		$response["error"] = 0;
		$response["success"] = 1;
		$response["vehicle_types"] = $trip_models;
	}else{
		$response["error"] = 1;
		$response["success"] = 0;
		$response["message"] = "no trip models avilable";
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
