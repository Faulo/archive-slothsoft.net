<?php

$client = \DBMS\Manager::getClient();

$dbList = $client->getDatabaseList();
foreach ($dbList as $db) {
	
}

//$db = \DBMS\Manager::getDatabase('twitter');

//$res = $db->resetCharset();

//my_dump($res);