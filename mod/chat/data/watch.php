<?php
namespace Slothsoft\CMS;

use Slothsoft\Chat\Model;

$dbName = 'cms';
$tableName = 'minecraft_log';

if ($name = $this->httpRequest->getInputValue('chat-database') and $name !== $tableName) {
	$dbName = 'chat';
	$tableName = $name;
}

$chat = new Model();
$chat->init($dbName, $tableName);

$start = $this->httpRequest->time;
$end = $start + 86400;

$chat->wait($start);

return $chat->getRangeNode($start, $end, $dataDoc);