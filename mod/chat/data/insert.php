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
$chat->insert(
	$this->httpRequest->getInputJSON(),
	$this->httpRequest->time,
	$this->httpRequest->clientIp
);
$this->httpResponse->setStatus(204);
