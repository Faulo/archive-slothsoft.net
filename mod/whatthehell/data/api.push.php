<?php
require_once('../../../../constants.php');

if (!isset($_REQUEST['type'])) {
	$_REQUEST['type'] = 'message';
}
if (!isset($_REQUEST['handle'])) {
	header('HTTP/1.1 400 Bad Request');
	die('400 Bad Request');
}

$handle = $_REQUEST['handle'];

$insert = array(
	'time' => $_SERVER['REQUEST_TIME_FLOAT'],
	'data' => file_get_contents('php://input'),
	'type' => $_REQUEST['type']
);
//$insert['data'] = 'Hallo Welt ' . $_SERVER['REQUEST_TIME'];

if (strlen($insert['data'])) {
	DB::setDatabase('whatthehell');
	DB::insert($handle, $insert);
}

header("HTTP/1.1 204 No Content"); 
die();
?>