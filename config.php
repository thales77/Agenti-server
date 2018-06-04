<?php
header('Content-Type: text/html; charset=utf-8');
ini_set( "log_errors", true );
ini_set( "error_reporting", E_ALL );
ini_set( "error_log", "/error_log" );

date_default_timezone_set( "Europe/Rome" );
define( "APP_URL", "index.php" );
define( "DB_DSN", "mysql:host=localhost;dbname=db" ); //Change accordingly
define( "DB_USERNAME", "user" ); //Change accordingly
define( "DB_PASSWORD", "password" ); //Change accordingly
define( "CLASS_PATH", "classes" );
define( "TEMPLATE_PATH", "templates" );
define( "PASSWORD_EMAIL_FROM_NAME", "" ); //Change accordingly
define( "PASSWORD_EMAIL_FROM_ADDRESS", "" ); //Change accordingly
define( "PASSWORD_EMAIL_SUBJECT", "" ); //Change accordingly
define( "PASSWORD_EMAIL_APP_URL", "" ); //Change accordingly
require( CLASS_PATH . "/User.php" );
require( CLASS_PATH . "/Client.php" );
require( CLASS_PATH . "/Item.php" );
require( CLASS_PATH . "/SalesHistory.php" );
require( CLASS_PATH . "/Log.php" );
require( CLASS_PATH . "/Order.php" );
?>
