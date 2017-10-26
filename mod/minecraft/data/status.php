<?php
namespace Slothsoft\CMS;

use Slothsoft\DBMS\Manager;
use Slothsoft\Minecraft\Log;
use DOMDocument;
$dbName = 'cms';
$tableName = 'minecraft_log';

$dbmsTable = Manager::getTable($dbName, $tableName);

$timeStart = $this->httpRequest->time - TIME_MONTH;

$messageTypes = [
    'login' => Log::$messageTypes['login'],
    'logout' => Log::$messageTypes['logout'],
    'boot' => Log::$messageTypes['boot'],
    'shutdown' => Log::$messageTypes['shutdown']
];
// my_dump(\Minecraft\Log::resetTable($tableName));

$res = $dbmsTable->select(true, sprintf('type IN (%s) AND time > %d ORDER BY time DESC', implode(',', $messageTypes), $timeStart));

$res = array_reverse($res);
// my_dump($res);
$playersTotal = array();
$playersOnline = array();
$lastSystem = null;
$version = null;
foreach ($res as $arr) {
    if ($arr['speaker']) {
        $player = $arr['speaker'];
        if ($arr['type'] == $messageTypes['login']) {
            $playersTotal[$player] = $arr['time'];
            $playersOnline[$player] = true;
        }
        if ($arr['type'] == $messageTypes['logout']) {
            unset($playersOnline[$player]);
        }
    }
    if (strpos($arr['raw'], 'SEVERE')) {
        continue;
    }
    if ($arr['type'] == $messageTypes['boot']) {
        $playersOnline = array();
        if (preg_match('/\((.+)\)/', $arr['message'], $match)) {
            $version = $match[1];
        }
    }
    if ($arr['type'] == $messageTypes['shutdown']) {
        $playersOnline = array();
        $isOn = false;
    }
    if ($arr['type'] == $messageTypes['boot'] or $arr['type'] == $messageTypes['shutdown']) {
        $lastSystem = $arr;
    }
}
arsort($playersTotal);

// $times = array_reverse($times);
$dataDoc = new DOMDocument('1.0', 'UTF-8');
$dataRoot = $dataDoc->createElement('status');
$dataDoc->appendChild($dataRoot);
foreach ($playersTotal as $player => $time) {
    $parent = $dataDoc->createElement('player');
    $parent->setAttribute('date-stamp', $time);
    $parent->setAttribute('date-datetime', date(DATE_DATETIME, $time));
    if (isset($playersOnline[$player])) {
        $parent->setAttribute('online', '');
    }
    $parent->setAttribute('name', $player);
    $dataRoot->appendChild($parent);
}
if ($lastSystem) {
    $parent = $dataDoc->createElement('system');
    $parent->setAttribute('date-stamp', $lastSystem['time']);
    $parent->setAttribute('date-datetime', date(DATE_DATETIME, $lastSystem['time']));
    $parent->setAttribute('message', $lastSystem['message']);
    if ($lastSystem['type'] == $messageTypes['boot'] or true) { // todo: re-implement online boot message
        $parent->setAttribute('online', '');
    }
    if ($version) {
        $parent->setAttribute('version', $version);
    }
    $dataRoot->appendChild($parent);
}

return $dataDoc;