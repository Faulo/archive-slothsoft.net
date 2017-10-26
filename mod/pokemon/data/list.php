<?php
$monDoc = $this->getResourceDoc('/pokemon/list', 'xml');

$start = $this->httpRequest->getInputValue('start', 1);
$end = $this->httpRequest->getInputValue('end', 10000);
$filter = (array) $this->httpRequest->getInputValue('filter', []);
$statRange = $this->httpRequest->getInputValue('stat-range', '');

$filterList = [];
$filterList['genderless'] = 'http://bulbapedia.bulbagarden.net/wiki/Category:Genderless_Pok%C3%A9mon';
$filterList['mythical'] = 'http://bulbapedia.bulbagarden.net/wiki/Category:Mythical_Pok%C3%A9mon';
$filterList['legendary'] = 'http://bulbapedia.bulbagarden.net/wiki/Category:Legendary_Pok%C3%A9mon';

if ($filter) {
    $noList = [];
    $excludeList = [];
    foreach ($filter as $key => $val) {
        $val = (int) $val;
        if (isset($filterList[$key])) {
            if ($xpath = $this->loadExternalXPath($filterList[$key], TIME_DAY)) {
                $host = 'http://' . parse_url($filterList[$key], PHP_URL_HOST);
                $linkNodeList = $xpath->evaluate('//*[@id="mw-pages"]//@href');
                foreach ($linkNodeList as $linkNode) {
                    if ($uri = $linkNode->value) {
                        if ($tmpPath = $this->loadExternalXPath($host . $uri, TIME_YEAR)) {
                            $no = $tmpPath->evaluate('substring-after(//*[@width="25%"], "#")');
                            $no = trim($no);
                            if ($no = (int) $no) {
                                if ($val) {
                                    $noList[] = $no;
                                } else {
                                    $excludeList[] = $no;
                                }
                            } else {
                                // my_dump($host . $uri);
                            }
                        }
                    }
                }
            }
        }
    }
    $noList = array_diff($noList, $excludeList);
} else {
    $noList = range($start, $end);
}

if (preg_match('/(\d+)-(\d+)/', $statRange, $match)) {
    $statMin = (int) $match[1];
    $statMax = (int) $match[2];
} else {
    $statMin = 0;
    $statMax = 1000;
}

$retFragment = $dataDoc->createDocumentFragment();

$monNodeList = $monDoc->getElementsByTagName('mon');
foreach ($monNodeList as $monNode) {
    $no = $monNode->getAttribute('no');
    if (in_array($no, $noList)) {
        $stat = (int) $monNode->getAttribute('stat-total');
        if ($stat >= $statMin and $stat <= $statMax) {
            $retFragment->appendChild($dataDoc->importNode($monNode, true));
        }
    }
}

return $retFragment;