<?php

$pageList = [];

$seriesURL = 'http://teamfourstar.com/series/dragonball-z-abridged/page/%d/';

for ($i = 1, $continue = true; $continue; $i++) {
	$continue = false;
	$url = sprintf($seriesURL, $i);
	if ($xpath = \Storage::loadExternalXPath($url, TIME_HOUR)) {
		$nodeList = $xpath->evaluate('//*[@class="archiveitems"]//*[@href][@rel="bookmark"]');
		foreach ($nodeList as $node) {
			$url = $node->getAttribute('href');
			if (!isset($pageList[$url])) {
				$pageList[$url] = $url;
				$continue = true;
			}
		}
	} else {
		die($url);
	}
}

$descList = [];
foreach ($pageList as $url) {
	if ($xpath = \Storage::loadExternalXPath($url, TIME_MONTH)) {
		$descList[$url] = $xpath->evaluate('string(//*[@class="postcontent"]/*[1])');
	} else {
		//echo $url . PHP_EOL;
		$html = \Storage::loadExternalFile($url, TIME_MONTH);
		$doc = new DOMDocument();
		@$doc->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_PARSEHUGE | LIBXML_HTML_NODEFDTD);
		//echo PHP_EOL . PHP_EOL;
		
		$doc->loadXML($doc->saveXML(), LIBXML_PARSEHUGE);
		
		output($doc);
	}
}

$urlList = [];

$blackList = ['-kai-', '-deleted-', 'nappafinger'];

foreach ($descList as $url => $desc) {
	//echo $url . ': ' . $desc . PHP_EOL;
	if (preg_match('/Episode (\d+):(.+)/', $desc, $match)) {
		$blacklisted = false;
		foreach ($blackList as $black) {
			if (strpos($url, $black) !== false) {
				$blacklisted = true;
			}
		}
		if ($blacklisted) {
			continue;
		}
		$no = (int) $match[1];
		$name = trim($match[2]);
		$key = sprintf('%03d %s', $no, $name);
		$val = sprintf('%s %s', $name, $url);
		$urlList[$key] = $val;
	} else {
		echo $url . PHP_EOL . "\t" . $desc . PHP_EOL . PHP_EOL;
	}
}

ksort($urlList);

foreach ($urlList as $url) {
	echo $url . PHP_EOL;
}