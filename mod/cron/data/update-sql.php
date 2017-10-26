<?php
namespace Slothsoft\CMS;

use Slothsoft\DBMS\Manager;

$client = Manager::getClient();

$dbList = $client->getDatabaseList();
foreach ($dbList as $db) {}

//$db = \DBMS\Manager::getDatabase('twitter');

//$res = $db->resetCharset();

//my_dump($res);