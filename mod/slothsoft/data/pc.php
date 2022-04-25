<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\FileSystem;
use Slothsoft\Core\Storage;

$dataPath = self::loadXPath($dataDoc);
$retNode = $dataDoc->createDocumentFragment();

$tempDoc = $this->getResourceDoc('slothsoft/pc', 'xml');
$dataNodeList = $tempDoc->getElementsByTagName('data');
foreach ($dataNodeList as $dataNode) {
    $retNode->appendChild($dataDoc->importNode($dataNode, true));
}
unset($tempDoc);

$tempDir = realpath(dirname(__FILE__) . '/../res/pc/') . DIRECTORY_SEPARATOR;

$todayDate = date('Y-m-d');
// $todayDate = '2011-11-13';

$partList = $dataPath->evaluate('.//part', $retNode);
$partNames = array();
foreach ($partList as $part) {
    if ($part->hasAttribute('price-uri')) {
        $uri = $part->getAttribute('price-uri');
        $partNames[] = $part->getAttribute('name');
        $dateFile = $tempDir . $todayDate . '.' . $part->getAttribute('name') . '.xml';
        
		continue;
		
        if (!$part->parentNode->hasAttribute('final-price')) {
			if (! is_file($dateFile)) {
				echo $dateFile . PHP_EOL;
				if ($priceDoc = Storage::loadExternalDocument($uri, TIME_DAY)) {
					$priceDoc->save($dateFile);
					echo 'SUCCESS';
				} else {
					echo $uri . PHP_EOL;
				}
				die;
			}
        }
    }
}

$prices = []; //FileSystem::scanDir($tempDir);
foreach ($prices as $file) {
    $arr = explode('.', $file);
    $date = $arr[0];
    $name = $arr[1];
    if (! in_array($name, $partNames)) {
        continue;
    }
    $dateFile = $tempDir . $file;
    $partList = $dataPath->evaluate(sprintf('.//part[@name="%s"]', $name), $retNode);
    if ($partList->length) {
        if ($priceDoc = $this->loadDocument($dateFile) and $pricePath = $this->loadXPath($priceDoc)) {
            // my_dump($priceDoc->saveXML());
            // $resNode = $pricePath->evaluate('//tr[@class="listBGdark first"][1]/td[2]//a[1]/text()');
            // $resText = $resNode->item(0)->wholeText
            if ($date === $todayDate) {
                // my_dump($pricePath->evaluate('count(//*[@id = "ProductInfoComponent-pricerange"])'));
            }
            if ($resText = $pricePath->evaluate('string(//*[@class = "listBGdark first"][1]//*[@href] | //*[@id = "ProductInfoComponent-pricerange"])')) {
                
                if (preg_match('/\d+,\d+/', $resText, $match)) {
                    $price = (float) str_replace(',', '.', $match[0]);
                    
                    // $parts = $dataPath->evaluate('//part[@name="'.$name.'"]');
                    
                    foreach ($partList as $part) {
                        if (! $part->hasAttribute('price-skip')) {
                            $part->setAttribute('price', $price);
                        }
                        $priceNode = $dataDoc->createElement('price');
                        $priceNode->setAttribute('price', $price);
                        $priceNode->setAttribute('date', $date);
                        $part->appendChild($priceNode);
                    }
                } else {
                    // my_dump($resNode->wholeText);
                }
            } else {
                // my_dump($priceDoc->saveXML());
            }
        }
    }
}

return $retNode;