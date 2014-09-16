<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
	exit();
}

class VehicleLocationLog {

	private $connection;
	public  $error_description;

	function __construct() {
		require_once dirname(__FILE__) . '/class_connection.php';
		$db = New Connection();
		$this->connection = $db->connect();
	}

	
	public function locate_taxi($trip_id)
	{
		$strSQL = "SELECT * FROM vehicle_locations_log";
		$strSQL .= " WHERE id = (SELECT MAX(id) FROM vehicle_locations_log WHERE trip_id = '".mysql_real_escape_string($trip_id)."') LIMIT 1";
		
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if ( mysql_num_rows($rsRES) == 1 ){
			return mysql_fetch_assoc($rsRES);
		}else{
			$this->error_description = "Invalid Trip";
			return false;
		}
	}
	
	

}
?>
