<?php

$dom = new \DOMHelper();

$url = 'https://twitter.com/i/tweet/html?id=643752378756370432&modal=gallery';

$data = \Storage::loadExternalJSON($url);
$html = $data['tweet_html'];

//data-url
//data-img-src

return $dom->parse($html, null, true);

return \CMS\HTTPFile::createFromString($html);


use \DBMS\Manager as DBMS;

$dbName = 'twitter';
$tableName = 'wasser_stille';

$dbmsTable = DBMS::getTable($dbName, $tableName);


$html = implode(PHP_EOL, $dbmsTable->select('tweet_html'));

print_execution_time();

for ($i = 0; $i < 100; $i++) {
	$dom->parse($html, null, false);
}

print_execution_time();

for ($i = 0; $i < 100; $i++) {
	$dom->parse($html, null, true);
}

print_execution_time();