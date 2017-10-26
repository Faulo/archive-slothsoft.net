<?php
namespace Slothsoft\CMS;

$userKey = 'slothsoft/japanese-faulo';

$tmpDoc = $this->getResourceDoc($userKey, 'xml');
$tmpPath = self::loadXPath($tmpDoc);

$translator = new \Lang\TranslatorJaEn();
$translator->commonWords = false;

$res = $translator->translateWord('仲直り');
// my_dump($res);
// die();
//
$nodeList = $tmpPath->evaluate('//html:tbody[@name != "Substantive - する Verben"]/html:tr/html:td[1]');

$res = [];

foreach ($nodeList as $node) {
    $kanji = $tmpPath->evaluate('normalize-space(.)', $node);
    $kana = $tmpPath->evaluate('normalize-space(following-sibling::*)', $node);
    if (strlen($kanji)) {
        $tmpList = $translator->translateWord($kanji);
        foreach ($tmpList as $tmp) {
            // echo $tmp['kana'] . PHP_EOL . $tmp['kanji'] . PHP_EOL . PHP_EOL;
            if ($tmp['kanji'] === $kanji) {
                $res[] = $tmp;
                // continue 2;
            }
        }
    }
    if (strlen($kana)) {
        $tmpList = $translator->translateWord($kana);
        foreach ($tmpList as $tmp) {
            // echo $tmp['kana'] . PHP_EOL . PHP_EOL;
            if ($tmp['kanji'] === $kanji and $tmp['kana'] === $kana) {
                $res[] = $tmp;
                // continue 2;
            }
        }
    }
}
$tagList = [];
foreach ($res as $word) {
    foreach ($word['tags'] as $tag) {
        $tagList[$tag] = true;
    }
    if (in_array('Suru verb', $word['tags']) or in_array('Suru verb - irregular', $word['tags'])) {
        echo $word['kana'] . PHP_EOL . $word['kanji'] . PHP_EOL . PHP_EOL;
    }
}

$tagList = array_keys($tagList);
natsort($tagList);

my_dump($tagList);