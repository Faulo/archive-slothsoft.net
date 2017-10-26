<?php

use Slothsoft\DBMS\Manager;

$dbName = 'cms';
$tableName = 'access_log';

$dbmsTable = Manager::getTable($dbName, $tableName);

$countList = [];
$uriList = $dbmsTable->select('REQUEST_URI', 'REQUEST_URI LIKE "/get%"');

foreach ($uriList as $uri) {
	$uri = explode('/', $uri);
	array_shift($uri);
	$key = '';
	foreach ($uri as $k) {
		$key .= '/' . $k;
		if (!isset($countList[$key])) {
			$countList[$key] = 0;
		}
		$countList[$key]++;
	}
}

ksort($countList);

foreach ($countList as $key => $val) {
	if ($val > 100) {
		printf('%6d: %s%s', $val, $key, PHP_EOL);
	}
}
die();