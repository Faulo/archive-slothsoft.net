<?php
$idealoURI = 'http://www.idealo.de/preisvergleich/ProductCategory/19116.html?q=';
$host = 'http://www.gsmarena.com/';
$startURI = 'http://www.gsmarena.com/results.php3?chkCardSlot=selected&sAvailabilities=1&sOSes=2&sOSversions=2600&sWLANs=3';

$uriList = [];
if ($xpath = $this->loadExternalXPath($startURI)) {
    $aNodeList = $xpath->evaluate('//*[@id="review-body"]//*[@href]');
    foreach ($aNodeList as $aNode) {
        $uriList[] = $host . $aNode->getAttribute('href');
    }
}

$filter = $this->httpRequest->getInputValue('filter', []);

$dataList = [];
$categoryList = [];
$categoryList['name'] = [];
$categoryList['resolution'] = [];
$categoryList['chipset'] = [];
$categoryList['camera'] = [];
$categoryList['battery'] = [];

$queryList = [];
$queryList['name'] = '//*[@class="specs-phone-name-title"]';
$queryList['resolution'] = '//*[@class="help accented help-display"]/text()[last()]';
$queryList['chipset'] = '//*[@class="help accented help-expansion"]/text()[last()]';
$queryList['camera'] = '//*[@class="accent accent-camera"]';
$queryList['battery'] = '//*[@class="accent accent-battery"]';

foreach ($uriList as $uri) {
    if ($xpath = $this->loadExternalXPath($uri, TIME_YEAR)) {
        $data = [];
        foreach ($queryList as $key => $query) {
            $query = sprintf('normalize-space(%s)', $query);
            $val = $xpath->evaluate($query);
            $data[$key] = $val;
        }
        $data['href-gsmarena'] = $uri;
        $data['href-idealo'] = $idealoURI . urlencode($data['name']);
        
        foreach ($filter as $key => $val) {
            if (strlen($val) and $data[$key] !== $val) {
                continue 2;
            }
        }
        $dataList[] = $data;
        foreach ($data as $key => $val) {
            if (isset($categoryList[$key])) {
                $categoryList[$key][$val] = $val;
            }
        }
    } else {
        throw new Exception("URI not found? $uri");
    }
}

$retFragment = $dataDoc->createDocumentFragment();

foreach ($categoryList as $key => $category) {
    natsort($category);
    $parentNode = $dataDoc->createElement('category');
    $parentNode->setAttribute('name', $key);
    foreach ($category as $val) {
        $node = $dataDoc->createElement('option');
        $node->setAttribute('name', $val);
        $parentNode->appendChild($node);
    }
    $retFragment->appendChild($parentNode);
}

foreach ($dataList as $data) {
    $parentNode = $dataDoc->createElement('smartphone');
    foreach ($data as $key => $val) {
        $parentNode->setAttribute($key, $val);
    }
    $retFragment->appendChild($parentNode);
}

foreach ($filter as $key => $val) {
    if (strlen($val)) {
        $parentNode = $dataDoc->createElement('filter');
        $parentNode->setAttribute('name', $key);
        $parentNode->setAttribute('value', $val);
        $retFragment->appendChild($parentNode);
    }
}

return $retFragment;