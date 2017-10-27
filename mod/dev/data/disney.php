<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\Storage;

$nameList = [];
$uri = 'http://disney.wikia.com/wiki/Category:Females?page=';

foreach (range(1, 27) as $page) {
    if ($xpath = Storage::loadExternalXPath($uri . $page, TIME_YEAR)) {
        $nodeList = $xpath->evaluate('//*[@id="mw-pages"]//*[@class="mw-content-ltr"]//*[@href]');
        foreach ($nodeList as $node) {
            $nameList[] = ' ' . $xpath->evaluate('normalize-space(.)', $node) . ' ';
        }
    }
}

$blackList = [];
$blackList[] = 'Madame';
$blackList[] = 'Golden';
$blackList[] = 'Gilded';
$blackList[] = 'Milady';

sort($nameList);

foreach ($nameList as $name) {
    $name = preg_replace('/\(.+/', '', $name);
    $name = preg_replace('/ and .+/', '', $name);
    if (preg_match('/^\s([A-Z][a-z]{0,3}d[a-z]{0,3}[b-z])\s/', $name, $match)) {
        $firstName = $match[1];
        if (strlen($firstName) === 6 and ! in_array($firstName, $blackList)) {
            echo $firstName . PHP_EOL;
            $blackList[] = $firstName;
        }
    }
}