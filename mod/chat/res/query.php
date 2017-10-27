<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/constants.php');

const MAX_EXECUTION_TIME = 300;
const TIMEOUT_MAX	= 200;
const TIMEOUT_STEP	= 1;
const FILE_LOG = 'chat.json';
const SEND_MAX = 100;
const TIME_FORMAT = 'd.m.y H:i:s';

const LOG_DB 	= 'cms';
const LOG_TABLE = 'minecraft_log';

set_time_limit(MAX_EXECUTION_TIME);
date_default_timezone_set('Europe/Berlin'); 

function appendDB(&$txt) {
	$log = loadDB();
	$log[] = array(
		time(),
		$txt,
		$_SERVER['REMOTE_ADDR']
	);
	$rcon = new RCon('127.0.0.1', '25575', 'sl0ths0ft@rcon');
	$rcon->execute('say §3'.utf8_decode($txt));
	\DB::insert(
		LOG_TABLE, 
		array('time' => time(), 'message' => $txt, 'ip' => $_SERVER['REMOTE_ADDR'], 'type' => \Minecraft\Log::$messageTypes['rcon'])
	);
	return saveDB($log);
}
function loadDB() {
	return json_decode(file_get_contents(FILE_LOG), true);
}
function saveDB(array &$arr) {
	return file_put_contents(FILE_LOG, json_encode($arr));
}

$time = time();
$Query = json_decode(file_get_contents('php://input'), true);

$Response = array(
	'error' => 1,
	'time' => $time
);

$messageTypes = [
	'system' => \Minecraft\Log::$messageTypes['system'],
	'chat' => \Minecraft\Log::$messageTypes['chat'],
	'god' => \Minecraft\Log::$messageTypes['god'],
	'rcon' => \Minecraft\Log::$messageTypes['rcon']
];

if (isset($Query['mode'])) {
	\DB::setDatabase(LOG_DB);
	switch ($Query['mode']) {
		case 'get':
			$Response['id'] = $id = (int) $Query['id'];
			$Response['size'] = $size = (int) $Query['size'];
			$send = array();
			if ($size === 0) {
				$Response['size'] = 0;
				/*
				$log = loadDB();
				$Response['id'] = count($log);
				if (SEND_MAX) {
					for ($j = count($log), $i = max($j - SEND_MAX, 0); $i < $j; $i++) {
						$send[] = array(date(TIME_FORMAT, $log[$i][0]), date(DATE_ATOM, $log[$i][0]), $log[$i][1], $log[$i][2]);
						//$send[] = $log[$i];
					}
				} else {
					foreach ($log as $arr) {
						$send[] = array(date(TIME_FORMAT, $arr[0]), date(DATE_ATOM, $log[$i][0]), $arr[1], $arr[2]);
						//$send[] = $arr;
					}
				}
				*/
				$res = \DB::select(LOG_TABLE, true, 'type IN ('.implode(',', $messageTypes).') ORDER BY time DESC LIMIT ' . SEND_MAX);
				$res = array_reverse($res);
				foreach ($res as $arr) {
					$msg = $arr['message'];
					if ($msg === 'Server: stop') {
						continue;
					}
					if ($arr['speaker']) {
						$msg = '<'.$arr['speaker'].'> ' . $msg;
					}
					if ($arr['type'] == 3) {
						$msg = $arr['speaker'] . ' logged in';
						continue;
					}
					if ($arr['type'] == 4) {
						$msg = $arr['speaker'] . ' logged out';
						continue;
					}
					if (!$arr['ip']) {
						$ip = md5(md5($arr['speaker']));
						$arr['ip'] = array();
						for ($i = 0; $i < 4; $i++) {
							$arr['ip'][] = hexdec(substr($ip, 2*$i, 2));
						}
						$arr['ip'] = implode('.', $arr['ip']);
					}
					$send[] = array(date(TIME_FORMAT, $arr['time']), date(DATE_ATOM, $arr['time']), $msg, $arr['ip']);
					$Response['size'] = $arr['time'];
				}
			} else {
				for ($i = 0; $i < TIMEOUT_MAX; $i += TIMEOUT_STEP) {
					if ($res = \DB::select(LOG_TABLE, true, 'type IN ('.implode(',', $messageTypes).') AND time > '.$size.' ORDER BY time DESC LIMIT ' . SEND_MAX)) {
						$res = array_reverse($res);
						foreach ($res as $arr) {
							$msg = $arr['message'];
							if ($msg === 'Server: stop') {
								continue;
							}
							if ($arr['speaker']) {
								$msg = '<'.$arr['speaker'].'> ' . $msg;
							}
							if ($arr['type'] == 3) {
								$msg = $arr['speaker'] . ' logged in';
								continue;
							}
							if ($arr['type'] == 4) {
								$msg = $arr['speaker'] . ' logged out';
								continue;
							}
							if (!$arr['ip']) {
								$ip = md5(md5($arr['speaker']));
								$arr['ip'] = array();
								for ($i = 0; $i < 4; $i++) {
									$arr['ip'][] = hexdec(substr($ip, 2*$i, 2));
								}
								$arr['ip'] = implode('.', $arr['ip']);
							}
							$send[] = array(date(TIME_FORMAT, $arr['time']), date(DATE_ATOM, $arr['time']), $msg, $arr['ip']);
							$Response['size'] = $arr['time'];
						}
						if (count($send)) {
							break;
						}
					}
					sleep(TIMEOUT_STEP);
					//clearstatcache();
				}
			}			
			$Response['error'] = 0;
			$Response['append'] = $send;			
			break;
		case 'ins':
			if (strlen($txt = trim($Query['text']))) {
				if (appendDB($txt)) {
					$Response['error'] = 0;
				}
			}
			break;
	}
}
header('Content-Type: application/json');
die(json_encode($Response));

?>
