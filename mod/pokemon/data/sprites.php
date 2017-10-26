<?php

$spritesURI = 'http://www.bisafans.de/pokedex/gen.php';
$data = [
	'auswahl' => '1',
];

$filterList = [];
$filterList['genderless'] = 'http://bulbapedia.bulbagarden.net/wiki/Category:Genderless_Pok%C3%A9mon';
$filterList['mythical'] = 'http://bulbapedia.bulbagarden.net/wiki/Category:Mythical_Pok%C3%A9mon';
$filterList['legendary'] = 'http://bulbapedia.bulbagarden.net/wiki/Category:Legendary_Pok%C3%A9mon';

$source = 'gen7/icon/statisch';
$targetList = [];
$targetList[1] = 'gen1/rb/normal';
$targetList[2] = 'gen2/kristall/normal';
$targetList[3] = 'gen3/smaragd/normal';
$targetList[4] = 'gen4/hgss/normal';
$targetList[5] = 'gen5/sw/normal';
//$targetList[6] = 'gen6/xy/normal';
$targetList[0] = 'gen7/icon/statisch';
//$targetList[6] = 'gen6/xy/normal';

$gen = $this->httpRequest->getInputValue('gen', 0);
$start = $this->httpRequest->getInputValue('start', 1);
$end = $this->httpRequest->getInputValue('end', 1000);
$filter = (array) $this->httpRequest->getInputValue('filter', []);

if ($filter) {
	$noList = [];
	$excludeList = [];
	foreach ($filter as $key => $val) {
		$val = (int) $val;
		if (isset($filterList[$key])) {
			if ($xpath = $this->loadExternalXPath($filterList[$key], TIME_DAY)) {
				$host = 'http://' . parse_url($filterList[$key], PHP_URL_HOST);
				$linkNodeList = $xpath->evaluate('//*[@id="mw-pages"]//@href');
				foreach ($linkNodeList as $linkNode) {
					if ($uri = $linkNode->value) {
						if ($tmpPath = $this->loadExternalXPath($host . $uri, TIME_DAY)) {
							$no = $tmpPath->evaluate('substring-after(//*[@width="25%"], "#")');
							$no = trim($no);
							if ($no = (int) $no) {
								if ($val) {
									$noList[] = $no;
								} else {
									$excludeList[] = $no;
								}
							} else {
								//my_dump($host . $uri);
							}
						}
					}
				}
			}
		}
	}
	$noList = array_diff($noList, $excludeList);
} else {
	$noList = range($start, $end);
}


$monList = [];
if ($xpath = $this->loadExternalXPath($spritesURI, TIME_DAY)) {
	//icon/statisch => xy/normal
	$target = isset($targetList[$gen])
		? $targetList[$gen]
		: $source;
	
	$rowNodeList = $xpath->evaluate('/div/table/tbody/tr');
	$queryList = [];
	$queryList['no'] = 'td[1]';
	$queryList['img'] = 'td[2]/img/@src';
	$queryList['name-de'] = 'td[3]';
	$queryList['name-en'] = 'td[4]';
	$queryList['href-de'] = 'td/a/@href';
	$queryList['href-en'] = 'concat("http://bulbapedia.bulbagarden.net/w/index.php?title=Special:Search&search=", td[4])';
	foreach ($rowNodeList as $rowNode) {
		$mon = [];
		foreach ($queryList as $key => $query) {
			$mon[$key] = $xpath->evaluate(sprintf('normalize-space(%s)', $query), $rowNode);
		}
		$mon['no'] = (int) $mon['no'];
		//$mon['img'] = str_replace($source, $target , $mon['img']);
		
		if (in_array($mon['no'], $noList)) {
			$monList[] = $mon;
		}
	}
}

$retFragment = $dataDoc->createDocumentFragment();

foreach ($monList as $mon) {
	$node = $dataDoc->createElement('mon');
	foreach ($mon as $key => $val) {
		$node->setAttribute($key, $val);
	}
	foreach ($targetList as $gen => $target) {
		$childNode = $dataDoc->createElement('gen');
		$childNode->setAttribute('img', str_replace($source, $target , $mon['img']));
		$node->appendChild($childNode);
	}
	$retFragment->appendChild($node);
}

return $retFragment;