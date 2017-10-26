<?php

$requestURI = 'http://kisscartoon.me/Cartoon/Steven-Universe';
$targetDir = 'D:\\Media\\Web Original\\Steven Universe';

$cfs = new \CloudFlareScraper();
if ($xpath = $cfs->getXPath($requestURI)) {
	//echo $requestURI . PHP_EOL . $xpath->document->saveHTML() . PHP_EOL;
	
	$uriList = [];
	$nodeList = $xpath->evaluate('//*[@data-value]');
	foreach ($nodeList as $node) {
		$episodeName = $xpath->evaluate('normalize-space(.)', $node);
		$episodePath = str_replace([' ', '---'], '-', $episodeName);
		$uriList[$episodeName] = sprintf('%s/%s?id=%d&s=stream', $requestURI, $episodePath, $node->getAttribute('data-value'));
	}
	
	$uriList = array_reverse($uriList);
	
	$downloadList = [];
	foreach ($uriList as $episodeName => $episodeURI) {
		sleep(10);
		//echo $episodeURI . PHP_EOL;
		$downloadURI = null;
		
		if ($xpath = $cfs->getXPath($episodeURI)) {
			//$episodeName = $xpath->evaluate('normalize-space(//*[@selected])');
			$playerURI = $xpath->evaluate('normalize-space(//*[@allowfullscreen]/@src)');
			$playerURI = str_replace('embed-', '', $playerURI);
			//my_dump(\Storage::loadExternalHeader($playerURI));
			if ($playerURI) {
				//echo $playerURI . PHP_EOL;
				if ($xpath = \Storage::loadExternalXPath($playerURI)) {
					$downloadURI = $xpath->evaluate('normalize-space(//*[@href][. = "(download)"]/@href)');
				} else {
					echo '??  ' . $playerURI . PHP_EOL;
				}
			} else {
				//echo '??  ' . $episodeURI . PHP_EOL;
				//echo $xpath->document->saveHTML() . PHP_EOL;
			}
		} else {
			echo '??  ' . $episodeURI . PHP_EOL;
			echo '!!  they are on to me, aborting...' . PHP_EOL;
			break;
		}
		
		if ($downloadURI) {
			echo $episodeName . PHP_EOL;
			$downloadList[$episodeName] = $downloadURI;
		} else {
			//break;
		}
	}
	//my_dump($downloadList);
	foreach ($downloadList as $name => $downloadURI) {
		$path = $targetDir . DIRECTORY_SEPARATOR . $name . '.mp4';
		echo $downloadURI . PHP_EOL;
		echo $path . PHP_EOL;
		$file = \CMS\HTTPFile::createFromDownload($path, $downloadURI);
		echo $file
			? 'SUCCESS!' . PHP_EOL
			: 'ERROR!' . PHP_EOL;
		//my_dump(\Storage::loadExternalHeader($downloadURI));
	}
} else {
	echo '???' . PHP_EOL;
	echo $requestURI;
}