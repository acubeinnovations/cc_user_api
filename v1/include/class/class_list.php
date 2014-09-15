<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
	exit();
}

class Listing {

	private $connection;
	public  $error_description;

	function __construct() {
		require_once dirname(__FILE__) . '/class_connection.php';
		$db = New Connection();
		$this->connection = $db->connect();
	}

	
	public function vehicle_types()
	{
		$strSQL = "SELECT id,name FROM vehicle_types ORDER BY name ASC";
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if ( mysql_num_rows($rsRES) > 0 ){
			$list = array();
			while($row = mysql_fetch_assoc($rsRES)){
				$list[] = array('id'=>$row['id'],'name'=>$row['name']);
			}
			return $list;
		}else{
			return false;
		}
	}

	public function trip_models()
	{
		$strSQL = "SELECT id,name FROM trip_models ORDER BY name ASC";
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if ( mysql_num_rows($rsRES) > 0 ){
			$list = array();
			while($row = mysql_fetch_assoc($rsRES)){
				$list[] = array('id'=>$row['id'],'name'=>$row['name']);
			}
			return $list;
		}else{
			return false;
		}
	}
}
?>
