<?php

$repo = \PT\Repository::getInstance('dom');

$retNode = $repo->asNode($dataDoc);

return $retNode;