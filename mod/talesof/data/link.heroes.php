<?php
namespace Slothsoft\CMS;

use DOMDocument;

/*
 *
 * $url = 'https://docs.google.com/spreadsheets/d/1gIuZ1PT9R8PLBWLeIefTFfzTRJXfEr_n9_A7TOHH3Ug/export?format=csv';
 *
 * if ($file = $this->loadExternalFile($url, TIME_DAY)) {
 * my_dump($file);
 * }
 *
 * //
 */
$baseURL = 'http://tales-of-link.wikia.com/wiki/Category:Units?page=%d';

$charLinkList = [];

for ($i = 1; $i < 10; $i ++) {
    $url = sprintf($baseURL, $i);
    if ($xpath = $this->loadExternalXPath($url, TIME_DAY)) {
        $linkNodeList = $xpath->evaluate('//*[@id="mw-pages"]//*[@class="mw-content-ltr"]//*[@href]');
        if (! $linkNodeList->length) {
            break;
        }
        foreach ($linkNodeList as $linkNode) {
            // echo $linkNode->textContent . PHP_EOL;
			$href = $linkNode->getAttribute('href');
			if (strpos($href, '/wiki/JP_') === false) {
				$url = 'http://tales-of-link.wikia.com' . $href;
				$charLinkList[$url] = null;
			}
        }
    }
}

$charLinkList = array_keys($charLinkList);

$queryList = [];
$queryList['Name'] = '//aside/h2';
$queryList['Rarity'] = '//table[thead/tr/th = "Rarity"]/tbody/tr/td[1]//a/@title';
$queryList['Type'] = '//table[thead/tr/th = "Type"]/tbody/tr/td[2]//a/@title';

$queryList['ArteName'] = '//tr[th[normalize-space()="Arte"]]/td';
$queryList['ArteDescription'] = '//tr[th[normalize-space()="Arte"]]/following-sibling::tr/td';

$queryList['LeaderSkillName'] = '//tr[th[normalize-space()="Leader Skill"]]/td';
$queryList['LeaderSkillDescription'] = '//tr[th[normalize-space()="Leader Skill"]]/following-sibling::tr/td';

$queryList['ActiveSkillName'] = '//tr[th[normalize-space()="Active Skill"]]/td[1]';
$queryList['ActiveSkillCost'] = '//tr[th[normalize-space()="Active Skill"]]/td[2]';
$queryList['ActiveSkillDescription'] = '//tr[th[normalize-space()="Active Skill"]]/following-sibling::tr/td';

$charDataList = [];

foreach ($charLinkList as $charLink) {
	//echo $charLink . PHP_EOL;
    if ($xpath = $this->loadExternalXPath($charLink, TIME_MONTH)) {
        $tmpNodeList = $xpath->evaluate('//a[img]');
        foreach ($tmpNodeList as $tmpNode) {
            $tmpNode->textContent = $xpath->evaluate('concat(" ", img/@alt, " ")', $tmpNode);
        }
        $tmpNodeList = $xpath->evaluate('//br');
        foreach ($tmpNodeList as $tmpNode) {
            $tmpNode->textContent = ';' . PHP_EOL;
        }
        
        $charData = [];
        $charData['Wiki'] = $charLink;
        foreach ($queryList as $key => $query) {
            $charData[$key] = $xpath->evaluate(sprintf('normalize-space(%s)', $query));
        }
        if (preg_match('~\d+~u', $charData['ActiveSkillCost'], $match)) {
            $charData['ActiveSkillCost'] = (int) $match[0];
        } else {
            $charData['ActiveSkillCost'] = '?';
            // my_dump($charData);
            // break;
        }
        
        if (preg_match('~\[([^]]+)\] (\w+)~u', $charData['Name'], $match)) {
            $charData['Title'] = $match[1];
            $charData['Hero'] = $match[2];
        } else {
            //probably just japanese unit
            continue;
        }
        
        if (preg_match('~\d~u', $charData['Rarity'], $match)) {
            $charData['Rarity'] = (int) $match[0];
        } else {
            my_dump($charData);
            break;
        }
        
        $charDataList[] = $charData;
    }
}

// my_dump($charDataList);

$resDoc = new DOMDocument('1.0', 'UTF-8');
$resNode = $resDoc->createElement('heroes');
foreach ($charDataList as $charData) {
    $charNode = $resDoc->createElement('character');
    foreach ($charData as $key => $val) {
        // echo $key . ': ' . $val . PHP_EOL;
        $charNode->setAttribute($key, $val);
    }
    $resNode->appendChild($charNode);
}
$resDoc->appendChild($resNode);

return HTTPFile::createFromDocument($resDoc, 'link.heroes.xml');

//$this->setResourceDoc('talesof/link.heroes', $resDoc);
