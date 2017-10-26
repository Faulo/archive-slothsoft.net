<?php

$feedList = [];
$feedList[] = 'http://blip.tv/dragon-ball-z-abridged/rss';

$root = 'D:/NetzwerkDaten/Series/Backup - Downloads';
if (!is_dir($root)) {
	throw new Exception('??? ' . $root);
}
$root = realpath($root) . DIRECTORY_SEPARATOR;

set_time_limit(-1);

foreach ($feedList as $feed) {
	$dir = basename(dirname($feed));
	if (strlen($dir)) {
		$dir = $root . $dir . DIRECTORY_SEPARATOR;
		if (!is_dir($dir)) {
			mkdir($dir);
		}
		if ($xpath = self::loadExternalXPath($feed)) {
			$xpath->registerNamespace('media', 'http://search.yahoo.com/mrss/');
			$nodeList = $xpath->evaluate('//media:content[starts-with(@type, "video")]');
			foreach ($nodeList as $node) {
				$url = $node->getAttribute('url');
				$file = basename($url);
				$path = $dir . $file;
				if (file_exists($path)) {
					echo sprintf('%s already exists!%s', $path, PHP_EOL);
				} else {
					echo sprintf('Downloading %s...%s', $url, PHP_EOL);
					@$data = file_get_contents($url);
					if (strlen($data)) {
						echo 'Done! ';
						$size = file_put_contents($path, $data);
						echo sprintf('Saved %dkB to %s%s', $size/1024, $path, PHP_EOL);
					} else {
						echo 'FAILED!' . PHP_EOL;
					}
				}
				//break;
			}
		}
	}
}