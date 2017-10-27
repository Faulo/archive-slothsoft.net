<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\WebCrawler;

// return \Storage::loadExternalDocument('https://www.steinlese.de/img/favicon.png');

/*
 * $url = 'https://www.steinlese.de/Natursteininfos/Naturstein-Travertin-Preise/';
 * $res = \Storage::loadExternalHeader($url, 0, null, ['followRedirects' => false]);
 * my_dump($res);
 * die();
 * //
 */

/*
 * $xhr = new \XMLHttpRequest();
 * $xhr->followRedirects = 0;
 * $xhr->open('HEAD', 'https://www.steinlese.de/Natursteinhandel-Krefeld/Natursteinfliesen/Travertin-Fliesen/Noce-Mix-braun-geschliffen-ungespachtelt-guenstig-kaufen/', false);
 * $xhr->send();
 * my_dump($xhr->getAllResponseHeaders());
 * die();
 * //
 */
$url = 'https://www.steinlese.de/';
$url = 'http://slothsoft.net';

$crawler = new WebCrawler($url);
$crawler->maxDepth = 10000;
$crawler->maxDocs = 100000;
$crawler->maxTime = TIME_DAY;

$ret = $crawler->crawl();

return HTTPFile::createFromString($ret);