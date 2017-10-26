<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\FileSystem;

$dataDocList = $this->getResourceDir('slothsoft/vocab-ja', 'status');

$arr = [];

/*
$file = '44Gy44Go44KKJuOBsuOBqOOCig~~.mp3';
$dataDoc = $dataDocList[$file];
//$arr[$file] = \FileSystem::base64Decode(substr($file, 0, -4));
$path = $dataDoc->documentElement->getAttribute('realpath');
$data = file_get_contents($path);
$data = substr($data, 0, 10);
$arr[$file] = '';
for ($i = 0; $i < 10; $i++) {
	//$arr[$file] .= ord($data[$i]) . '-'; 
}
//*/
$byteMark = chr(255) . chr(251) . chr(144) . chr(196);
//my_dump(strpos($data, $byte));
//*
foreach ($dataDocList as $name => $dataDoc) {
	$file = $name . '.mp3';
	$size = (int) $dataDoc->documentElement->getAttribute('size');
	if ($size < 1000) {
		$arr[$file] = FileSystem::base64Decode($name);
	}
	/*
	$path = $dataDoc->documentElement->getAttribute('realpath');
	$data = file_get_contents($path);
	if (strpos($data, $byteMark) === 0) {
		$arr[$file] = \FileSystem::base64Decode(substr($file, 0, -4));
	}
	//*/
}
//*/
//ÿûÄ
//ÿó„Ä
my_dump($arr);