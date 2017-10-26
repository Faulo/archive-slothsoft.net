<?php
$url = 'http://podbay.fm/show/536258179';
$path = 

if ($xpath = \Storage::loadExternalXPath($url)) {
    $argsList = [];
    $nodeList = $xpath->evaluate('//a[@rel="tooltip"]');
    foreach ($nodeList as $node) {
        $url = $node->getAttribute('href');
        $name = trim($node->textContent);
        if (preg_match('/^(\d[^\s]+)/', $name, $match)) {
            $args = [];
            $args['url'] = $url;
            $args['path'] = $path;
            $argsList[] = $args;
        }
    }
    my_dump($urlList);
}

die();