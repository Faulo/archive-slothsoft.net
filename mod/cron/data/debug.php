<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\Storage;


// $uri = 'https://rg3.github.io/youtube-dl/download.html';
$uri = 'https://github.com/rg3/youtube-dl/releases/latest';
// $query = ".//a[contains(@href, '.exe')][1]/@href";
$query = ".//@href[contains(., '.exe')]";

$cacheTime = 0;

$xpath = Storage::loadExternalXPath($uri, $cacheTime);

$nodeList = $xpath->evaluate($query);

if ($nodeList->length) {
    foreach ($nodeList as $node) {
        echo $node->textContent . PHP_EOL;
    }
} else {
    // my_dump(Storage::loadExternalHeader($uri, $cacheTime));
    return $xpath->document;
}