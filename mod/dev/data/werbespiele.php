<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\Image;
use Slothsoft\Core\Storage;

$host = 'https://www.kultboy.com';

$index = 'werbespiele.html';

$indexDoc = new \DOMDocument();
$indexDoc->loadHTMLFile(__DIR__ . DIRECTORY_SEPARATOR . $index);

$uris = [];
foreach ($indexDoc->getElementsByTagName('a') as $node) {
	$uri = $node->getAttribute('href');
	if ($uri) {
		$uris[] = $host . $uri;
	}
}

foreach ($uris as $uri) {
    $xpath = Storage::loadExternalXPath($uri, TIME_YEAR);
    if ($xpath) {
		$title = $xpath->evaluate('normalize-space(//b[@class="font5"])');
		$rating = $xpath->evaluate('normalize-space(//b[contains(., "/")])');
		if ($rating === '10/10') {
			echo "$title: $uri" . PHP_EOL;
		}
    }
}