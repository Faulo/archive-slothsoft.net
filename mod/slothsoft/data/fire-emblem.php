<?php
namespace Slothsoft\CMS;

use Slothsoft\FireEmblem\Game;
$gameKey = $this->httpRequest->getInputValue('game');
if (! $gameKey) {
    return null;
}

$doc = $this->getResourceDoc('slothsoft/fire-emblem', 'xml');
if (! $doc) {
    return;
}

$docList = $this->getResourceDir('slothsoft/fire-emblem-data', 'xml');

$game = new Game($doc, $docList);

$game->initGame($gameKey);

return $game->asNode($dataDoc);