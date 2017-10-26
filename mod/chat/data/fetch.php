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

$duration = isset($this->httpRequest->input['chat-duration']) ? (int) $this->httpRequest->input['chat-duration'] : 3650;
$end = $this->httpRequest->time;
$start = $end - $duration * TIME_DAY;

return $chat->getRangeNode($start, $end, $dataDoc);