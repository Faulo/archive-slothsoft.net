<?php
use Slothsoft\PT\Repository;

$repo = Repository::getInstance('dom');

$retNode = $repo->asNode($dataDoc);

return $retNode;