<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
    exit();
}

// Mysql Configuration Constants

define('MYSQL_USERNAME', 'root');
define('MYSQL_PASSWORD', 'mysql@local');
define('MYSQL_HOST', 'localhost');
define('MYSQL_DB_NAME', 'connectncabs');


define('WALKIN_CUSTOMER','1');
define('APP_REGISTRATION','2');
define('ORG_CNC','1');

define('TRIP_STATUS_BOOKING','1');
define('BOOKING_SOURCE_APP','4');

?>
