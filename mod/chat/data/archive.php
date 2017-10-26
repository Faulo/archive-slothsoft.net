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

$start = isset($this->httpRequest->input['chat-start']) ? (int) $this->httpRequest->input['chat-start'] : 0;
$end = isset($this->httpRequest->input['chat-end']) ? (int) $this->httpRequest->input['chat-end'] : time();

return $chat->getRangeNode($start, $end, $dataDoc);