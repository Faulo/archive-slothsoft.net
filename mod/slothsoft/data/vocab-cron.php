<?php
namespace Slothsoft\CMS;

$uriList = [];
$uriList[] = 'http://slothsoft.net/Japanese/JLPT/N5/VocabularyList/';
$uriList[] = 'http://slothsoft.net/Japanese/JLPT/N4/VocabularyList/';
$uriList[] = 'http://slothsoft.net/Japanese/JLPT/N3/VocabularyList/';
$uriList[] = 'http://slothsoft.net/Japanese/JLPT/N2/VocabularyList/';
$uriList[] = 'http://slothsoft.net/Japanese/JLPT/N1/VocabularyList/';
$uriList[] = 'http://slothsoft.net/Japanese/PersonalStudies/Daniel/VocabularyList/';

foreach ($uriList as $uri) {
    echo $uri . '...';
    $ch = curl_init($uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
    echo ' OK' . PHP_EOL . PHP_EOL;
}