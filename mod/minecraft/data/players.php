<?php
use Slothsoft\Minecraft\NBT\TAGNode;

$dir = dirname(__FILE__) . '/../res/players/';
$arr = scandir($dir);
$doc = new \DOMDocument();
$root = $doc->createElement('data');
foreach ($arr as $file) {
    if ($file === '.' or $file === '..')
        continue;
    $content = file_get_contents($dir . $file);
    $Tag = TAGNode::createDocument($content);
    $node = TAGNode::TAG2DOM($doc, $Tag);
    $node->setAttribute('name', $file);
    $node->setAttribute('time', date('d.m.Y G:i', filemtime($dir . $file)));
    $root->appendChild($node);
}
$doc->appendChild($root);
header('content-type:application/xml');
die($doc->saveXML());
return $doc;