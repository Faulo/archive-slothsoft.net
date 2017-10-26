<?php

$spritesURI = 'http://www.bisafans.de/pokedex/gen.php';

$source = 'gen7/icon/statisch';

$targetList = [];
//*
$targetList['gen1'] = [
	'path' => 'gen1/rb/normal',
	'last' => 151,
];
$targetList['gen2'] = [
	'path' => 'gen2/kristall/normal',
	'last' => 251,
];
$targetList['gen3'] = [
	'path' => 'gen3/smaragd/normal',
	'last' => 386,
];
$targetList['gen4'] = [
	'path' => 'gen4/hgss/normal',
	'last' => 493,
];
$targetList['gen5'] = [
	'path' => 'gen5/sw/normal',
	'last' => 649,
];
$targetList['gen6'] = [
	'path' => 'gen6/xy/normal',
	'last' => 721,
];
$targetList['gen7'] = [
	'path' => 'gen7/sm/normal',
	'last' => 802,
];
//*/
$targetList['icon'] = [
	'path' => 'gen7/icon/statisch',
	'last' => 802,
];

$tempDir = realpath(__DIR__ . '/../res/sprites-raw');
$targetDir = realpath(__DIR__ . '/../res/sprites');

$monList = [];
if ($spriteXPath = $this->loadExternalXPath($spritesURI, TIME_DAY)) {
	$rowNodeList = $spriteXPath->evaluate('/div/table/tbody/tr');
	
	$spriteQueryList = [];
	$spriteQueryList['no'] = 'td[1]';
	$spriteQueryList['img'] = 'td[2]/img/@src';
	$spriteQueryList['name-de'] = 'td[3]';
	$spriteQueryList['name-en'] = 'td[4]';
	$spriteQueryList['href-de'] = 'td/a/@href';
	
	$wikiQueryList = [];
	$wikiQueryList['stat-total'] = '//th[normalize-space(.) = "Total:"]/following-sibling::th[1]';
	
	foreach ($rowNodeList as $rowNode) {
		$mon = [];
		foreach ($spriteQueryList as $key => $query) {
			$mon[$key] = $spriteXPath->evaluate(sprintf('normalize-space(%s)', $query), $rowNode);
		}
		$mon['no'] = (int) $mon['no'];
		$mon['href-en'] = sprintf('http://bulbapedia.bulbagarden.net/w/index.php?title=Special:Search&search=%s', rawurlencode($mon['name-en']));
		
		if ($wikiXPath = $this->loadExternalXPath($mon['href-en'], TIME_YEAR)) {
			echo $mon['href-en'] . PHP_EOL;
			foreach ($wikiQueryList as $key => $query) {
				$mon[$key] = $wikiXPath->evaluate(sprintf('normalize-space(%s)', $query));
			}
		}
		
		$monList[] = $mon;
	}
}

if ($tempDir and $targetDir) {
	$dataNode = $dataDoc->createElement('list');
	foreach ($monList as $mon) {
		$node = $dataDoc->createElement('mon');
		$last = 1;
		foreach ($targetList as $gen => $target) {
			if (!isset($target['first'])) {
				$target['first'] = $last;
			}
			if ($mon['no'] <= $target['last']) {
				$uri = 'http:' . str_replace($source, $target['path'], $mon['img']);
				$path = parse_url($uri, PHP_URL_PATH);
				//echo $path . PHP_EOL;
				$name = sprintf('%04d-%s', $mon['no'], $gen);
				$tempPath = $tempDir . DIRECTORY_SEPARATOR . $name . '.png';
				//echo $tempPath . PHP_EOL;
				if ($file = \CMS\HTTPFile::createFromDownload($tempPath, $uri)) {
					$file->copyTo(
						$targetDir,
						null
						/*
						,
						function($source, $target) {
							return \Image::convertFile($source, $target);
						}
						//*/
					);
					echo '! ' . $tempPath . PHP_EOL;
					$childNode = $dataDoc->createElement('image');
					$childNode->setAttribute('href', '/getResource.php/pokemon/sprites/' . $name);
					$childNode->setAttribute('type', $gen);
					$node->appendChild($childNode);
				} else {
					echo '? ' . $uri . PHP_EOL;
					//break 2;
				}
			}
			$last = $target['last'];
		}
		unset($mon['img']);
		foreach ($mon as $key => $val) {
			$node->setAttribute($key, $val);
		}
		$dataNode->appendChild($node);
	}
	$this->setResourceDoc('/pokemon/list', $dataNode);
}