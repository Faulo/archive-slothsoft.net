<?php
namespace Slothsoft\CMS;

use Slothsoft\DBMS\Manager;
use Slothsoft\Minecraft\Log;
use DOMDocument;

$dbName = 'cms';
$tableName = 'minecraft_log';

$dbmsTable = Manager::getTable($dbName, $tableName);

/*
my_dump(\DB::select($tableName, true, 'type > 0 AND speaker LIKE "%[%"'));die();

$res = \DB::select($tableName, true, 'type > 0 AND speaker LIKE "%[%"');
foreach ($res as $line) {
	$line['type'] = 0;
	\DB::update($tableName, $line, $line['id']);
}
my_dump($res);die();
//*/
$now = $this->httpRequest->time;
$now += 60;
$now -= $now % 60;

$timeStart = $now - TIME_DAY * 2;
$timeStop = $now;
$timeStep = TIME_HOUR;

$messageTypes = [
	'login' => Log::$messageTypes['login'],
	'logout' => Log::$messageTypes['logout'],
	'boot' => Log::$messageTypes['boot'],
	'shutdown' => Log::$messageTypes['shutdown'],
];

$res = $dbmsTable->select(
	true,
	sprintf('type IN (%s) AND time > %d ORDER BY time DESC', implode(',', $messageTypes), $timeStart)
);

$res = array_reverse($res);
$playersOnline = array();
$times = array();
$times[] = array(
	'time' => $timeStart,
	'players' => array()
);
foreach ($res as $arr) {
	$times[] = array(
		'time' => $arr['time'],
		'players' => $playersOnline
	);
	if ($arr['speaker']) {
		$player = $arr['speaker'];
		if ($arr['type'] == $messageTypes['login']) {
			$playersOnline[$player] = 1;
		}
		if ($arr['type'] == $messageTypes['logout']) {
			$playersOnline[$player] = 0;
		}
	}
	if ($arr['type'] == $messageTypes['boot']) {
		foreach ($playersOnline as &$val) {
			$val = 0;
		}
		unset($val);
	}
	$times[] = array(
		'time' => $arr['time'],
		'players' => $playersOnline
	);
}
$times[] = array(
	'time' => $timeStop,
	'players' => $playersOnline
);
//$times = array_reverse($times);
$dataDoc = new DOMDocument('1.0', 'UTF-8');
$dataRoot = $dataDoc->createElement('log');
$dataDoc->appendChild($dataRoot);
foreach ($times as $arr) {
	$time = $arr['time'];
	$players = $arr['players'];
	$parent = $dataDoc->createElement('online');
	$parent->setAttribute('date-stamp', $time);
	$players = array_filter($players);
	$parent->setAttribute('count', array_sum($players));
	$parent->setAttribute('players', implode(' ', array_keys($players)));
	$dataRoot->appendChild($parent);
}

for ($i = $timeStart; $i <= $timeStop; $i += $timeStep) {
	$time = round($i / $timeStep) * $timeStep;
	$parent = $dataDoc->createElement('clock');
	$parent->setAttribute('date-stamp', $time);
	$parent->setAttribute('date-time', substr(date(DATE_TIME, $time), 0, 5));
	$dataRoot->appendChild($parent);
}

return $dataDoc;

?>