<?php
namespace Slothsoft\CMS;

use Slothsoft\Chat\SSEServer;

$sseName = $this->httpRequest->getInputValue('name');
$sseMode = $this->httpRequest->getInputValue('mode');
$lastId = $this->httpRequest->getInputValue('lastId');
if ($id = $this->httpRequest->getHeader('HTTP_LAST_EVENT_ID')) {
	$lastId = $id;
}

$dbName = 'cms';
$tableName = 'minecraft_log';
if ($sseName and $sseName !== $tableName) {
	$dbName = 'chat';
	$tableName = $sseName;
}

$sse = new SSEServer($tableName, $dbName);
$sse->init($lastId);

$ret = null;

switch ($sseMode) {
	case 'push':
		$message = $this->httpRequest->getInputJSON();
		
		if ($this->isBanworthy($message)) {
			$this->addBanned();
			$this->httpResponse->setStatus(HTTPResponse::STATUS_PRECONDITION_FAILED);
			$this->progressStatus = self::STATUS_RESPONSE_SET;
			return;
		}
		
		$sse->dispatchEvent(
			$this->httpRequest->getInputValue('type'),
			$message,
			$this->httpRequest->time,
			$this->httpRequest->clientIp
		);
		$this->httpResponse->setStatus(HTTPResponse::STATUS_NO_CONTENT);
		$this->progressStatus = self::STATUS_RESPONSE_SET;
		break;
	case 'pull':
		$ret = $sse->getStream();
		break;
	case 'last':
		$ret = $sse->fetchLastEvent();
		$ret = HTTPFile::createFromJSON($ret);
		break;
}

return $ret;