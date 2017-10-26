<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\XMLHttpRequest;
use DOMDocument;

$_REQUEST['dnt'] = 'false';
//require_once '../../../constants.php';


$dom = new DOMHelper();

//$uri = 'https://slothsoft.net';
/*
$req = new \XMLHttpRequest();
$req->open('POST', $uri);
$req->send($data);
$ret = $req->responseText;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $uri);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$ret = curl_exec($ch);

$curlPath = realpath('C:\NetzwerkDaten\Dropbox\Tools\curl');



$snoopy = new Snoopy();
$snoopy->curl_path = $curlPath;
$snoopy->submit($uri);
$ret = $snoopy->results;


$doc = \CMS\HTTPDocument::loadExternalDocument($uri, 'html', 0, $data, 'POST');
header('content-type: application/xhtml+xml; charset=UTF-8');
echo($doc);
//*/


$uri = 'https://socialshaker.net/photocontest2/ajaxSwitchPage.php?sid=14a1af3a79af5e82484c509d4bfebce0';
$data = [
	'page' => null,
	'method' => 'switchPage',
	'sort' => 'date_desc',
	'tab_id' => '555185',
];

//$dataDoc = new \DOMDocument();
//$dataRoot = $dataDoc->createElement('data');
//$dataDoc->appendChild($dataRoot);
$dataRoot = $dataDoc->createDocumentFragment();


for ($i = 1, $j = 2; $i < $j; $i++) {
	$data['page'] = $i;
	$req = new XMLHttpRequest();
	$req->open('POST', $uri);
	$req->send($data);

	$dataFragment = $dom->parse($req->responseText, $dataDoc, true);
	if ($dataFragment->hasChildNodes()) {
		$j++;
		$dataRoot->appendChild($dataFragment);
	}
}

//header('content-type: application/xhtml+xml; charset=UTF-8');
//$dataDoc->save('php://output');


$dataPath = self::loadXPath($dataDoc);

$skitList = [];
$skitNodeList = $dataPath->evaluate('script[contains(., "{")]', $dataRoot);
foreach ($skitNodeList as $skitNode) {
	$json = $skitNode->textContent;
	$json = strstr($json, '{');
	$skitList[] = json_decode($json, true);
	//my_dump($json);die();
}

//$dataDoc = new DOMDocument();
//$dataPath = new DOMXPath($dataDoc);
//$dataRoot = $dataDoc->createElement('data');
//$dataDoc->appendChild($dataRoot);
$dataRoot = $dataDoc->createDocumentFragment();

$langList = [
	'de' => ['german', 'deutsch'],
	'fr' => ['french', 'français'],
	'it' => ['italian', 'italiano', 'italia'],
	'es' => ['spanish', 'español', 'ESPAÑOL'],
	'en' => ['english', 'eng'],
	'unknown' => [],
];
foreach ($langList as $lang => $tmp) {
	$langNode = $dataDoc->createElement('lang');
	$langNode->setAttributeNS(self::NS_XML, 'lang', $lang);
	$dataRoot->appendChild($langNode);
}

foreach ($skitList as $skit) {
	$skitNode = $dataDoc->createElement('skit');
	foreach ($skit as $key => $val) {
		$skitNode->setAttribute($key, $val);
		$textNodeList = $dom->parse($val, $dataDoc, true);
		$keyNode = $dataDoc->createElement($key);
		$keyNode->appendChild($textNodeList);
		$skitNode->appendChild($keyNode);
	}
	$dataRoot->appendChild($skitNode);
	
	$title = $dataPath->evaluate('string(title)', $skitNode);
	$lang = 'unknown';
	foreach ($langList as $l => $arr) {
		foreach ($arr as $tmp) {
			if (stripos($title, $tmp) !== false) {
				$lang = $l;
				break 2;
			}
		}
	}
	$skitNode->setAttributeNS(self::NS_XML, 'lang', $lang);
	
	$desc = $dataPath->evaluate('string(description)', $skitNode);
	if (preg_match_all('/(https?:[^\s]+)/', $desc, $matches)) {
		$urlList = [];
		foreach ($matches[1] as $url) {
			if (strpos($url, '//imgur')) {
				$url = str_replace('imgur', 'i.imgur', $url);
				$url.= '.jpg';
			}
			if (strpos($url, 'i.imgur') and !isset($urlList[$url])) {
				$urlList[$url] = true;
				$imgNode = $dataDoc->createElement('link');
				$imgNode->setAttribute('href', $url);
				$imgNode->setAttribute('thumb', str_replace('.jpg', 's.jpg', $url));
				
				$skitNode->appendChild($imgNode);
			}
		}
	}
}
if ($dataPath->evaluate('count(*)', $dataRoot) > 100) {
	$tmpDoc = new DOMDocument();
	$node = $tmpDoc->createElement('data');
	$tmpDoc->appendChild($node);
	$node->appendChild($tmpDoc->importNode($dataRoot, true));
	$tmpDoc->save(dirname(__FILE__) . '/skitcontest.xml');
} else {
	$tmpDoc = new DOMDocument();
	$tmpDoc->load(dirname(__FILE__) . '/skitcontest.xml');
	$dataRoot = $dataDoc->createDocumentFragment();
	foreach ($tmpDoc->documentElement->childNodes as $node) {
		$dataRoot->appendChild($dataDoc->importNode($node, true));
	}
}

return $dataRoot;



$finalDoc = $dom->transform($dataDoc, 'contest.xsl');

header('content-type: application/xhtml+xml; charset=UTF-8');

$finalDoc->save('php://output');