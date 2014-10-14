<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
	exit();
}

class Trip {

	private $connection;
	public  $error_description;

	function __construct() {
		require_once dirname(__FILE__) . '/class_connection.php';
		$db = New Connection();
		$this->connection = $db->connect();
	}
	
	public function booking($dataArray = array())
	{
		if($dataArray){
			//new trip
			$strSQL = "INSERT INTO trips SET ";
			foreach($dataArray as $key=>$value){
				$strSQL .= $key."='".mysql_real_escape_string($value)."',";
			}
			$strSQL = substr($strSQL,0,-1);

			$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
			
			if(mysql_affected_rows($this->connection) == 1){
				$this->error_description = "Booking success";
				return mysql_insert_id();
			}else{
				$this->error_description = "Booking Failed";
				return false;
			}	
		}else{
			$this->error_description = "Invalid Trip details";
			return false;
		}
		
	}

	public function booking_details($id)
	{
		
		$strSQL = "SELECT * FROM trips WHERE id = '".mysql_real_escape_string($id)."'";
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if ( mysql_num_rows($rsRES) == 1 ){
			return mysql_fetch_assoc($rsRES);
		}else{
			$this->error_description = "Invalid Trip";
			return false;
		}
		
	}

	public function get_booking_details_by_customer($app_id,$IMEI,$token)
	{
		$strSQL = "SELECT cust.name AS name,trip.*";
		$strSQL .= " FROM trips trip,customers cust";
		$strSQL .= " WHERE cust.app_id = '".mysql_real_escape_string($app_id)."' AND cust.imei = '".mysql_real_escape_string($IMEI)."' AND cust.token = '".mysql_real_escape_string($token)."' AND cust.id = trip.customer_id";
		$strSQL .= " AND trip.organisation_id = ".ORG_CNC;
		$strSQL .= " ORDER BY trip.booking_date DESC";
		
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );

		$bookings = array();
		if ( mysql_num_rows($rsRES) > 0 ){
			while($row = mysql_fetch_assoc($rsRES)){
				
				$bookings[] = array(
						'id'	=> $row['id'],
						'name' => $row['name'],
						'from' => array(
								'city' => $row['pick_up_city'],
								'area' => $row['pick_up_area'],
								'landmark' => $row['pick_up_landmark']
								),
						'to' => array(
								'city' => $row['drop_city'],
								'area' => $row['drop_area'],
								'landmark' => $row['drop_landmark']
								),						
						'date' => strtotime($row['booking_date']." ".$row['booking_time']),
						'trip_date' => date('d-M-y h:i a',strtotime($row['pick_up_date']." ".$row['pick_up_time'])),
						'confirmation' => $row['trip_status_id']
						);
			}
			return $bookings;
		}else{
			$this->error_description = "Invalid Trip";
			return false;
		}

	}






}
?>
