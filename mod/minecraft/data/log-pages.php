<?php
namespace Slothsoft\CMS;

use Slothsoft\Chat\Model;

$retFragment = $dataDoc->createDocumentFragment();

$chat = new Model();
$chat->init('cms', 'minecraft_log');

$firstTime = $chat->getFirstTime();
$firstYear = (int) date('Y', $firstTime);
$firstMonth = (int) date('n', $firstTime);

$lastTime = $chat->getLastTime();
$lastYear = (int) date('Y', $lastTime);
$lastMonth = (int) date('n', $lastTime);

//my_dump($firstYear);
//my_dump($firstMonth);

//my_dump($chat->getFirstTime());
//my_dump($chat->getLastTime());

$startMonths = [];
$startMonths[$lastYear] = $lastMonth;
$stopMonths = [];
$stopMonths[$firstYear] = $firstMonth;

$pageNode = $dataDoc->createElement('page');
$pageAttr = [];
$pageAttr['status-active'] = '';
$pageAttr['status-public'] = '';
foreach ($pageAttr as $key => $val) {
	$pageNode->setAttribute($key, $val);
}


//actual <page>s
for ($year = $lastYear; $year >= $firstYear; $year--) {
	$startMonth = isset($startMonths[$year])
		? $startMonths[$year]
		: 12;
	$stopMonth = isset($stopMonths[$year])
		? $stopMonths[$year]
		: 1;
	$parentNode = $pageNode->cloneNode(true);
	$retFragment->appendChild($parentNode);
	$pageAttr = [];
	$pageAttr['redirect'] = '/Minecraft/Log/';
	$pageAttr['name'] = $year;
	$pageAttr['title'] = $year;
	foreach ($pageAttr as $key => $val) {
		$parentNode->setAttribute($key, $val);
	}

	for ($month = $startMonth; $month >= $stopMonth; $month--) {
		$childNode = $pageNode->cloneNode(true);
		$parentNode->appendChild($childNode);
		$pageAttr = [];
		$pageAttr['ref'] = 'archive';
		$pageAttr['name'] = str_pad($month, 2, '0', STR_PAD_LEFT);
		$pageAttr['title'] = 'month/'.str_pad($month, 2, '0', STR_PAD_LEFT);
		foreach ($pageAttr as $key => $val) {
			$childNode->setAttribute($key, $val);
		}
		
		$paramNode = $dataDoc->createElement('param');
		$paramNode->setAttribute('name', 'chat-start');
		$paramNode->setAttribute('value', mktime (0, 0, 0, $month, 1, $year));
		$childNode->appendChild($paramNode);
		
		$paramNode = $dataDoc->createElement('param');
		$paramNode->setAttribute('name', 'chat-end');
		$paramNode->setAttribute('value', mktime (0, 0, 0, $month+1, 1, $year));
		$childNode->appendChild($paramNode);
	}
}
$pageNode = $dataDoc->createElement('page');
$pageAttr = [];
$pageAttr['status-active'] = '';
$pageAttr['redirect'] = '/Minecraft/Log/';
foreach ($pageAttr as $key => $val) {
	$pageNode->setAttribute($key, $val);
}
//redirect <page>s
for ($year = $firstYear; $year >= 1970; $year--) {
	$startMonth = 12;
	$stopMonth = 1;
	$parentNode = $pageNode->cloneNode(true);
	$retFragment->appendChild($parentNode);
	$pageAttr = [];
	$pageAttr['name'] = $year;
	foreach ($pageAttr as $key => $val) {
		$parentNode->setAttribute($key, $val);
	}
	
	for ($month = $startMonth; $month >= $stopMonth; $month--) {
		$childNode = $pageNode->cloneNode(true);
		$parentNode->appendChild($childNode);
		$pageAttr = [];
		$pageAttr['name'] = str_pad($month, 2, '0', STR_PAD_LEFT);
		foreach ($pageAttr as $key => $val) {
			$childNode->setAttribute($key, $val);
		}
	}
}

return $retFragment;