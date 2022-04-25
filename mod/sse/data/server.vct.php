<?php
namespace Slothsoft\CMS;

use Slothsoft\SSE\VCTServer;
$sseName = $this->httpRequest->getInputValue('name');
$sseMode = $this->httpRequest->getInputValue('mode');
$lastId = $this->httpRequest->getInputValue('lastId');
if ($id = $this->httpRequest->getHeader('HTTP_LAST_EVENT_ID')) {
    $lastId = $id;
}
$userId = null;

$sse = new VCTServer($sseName);
$sse->init($lastId, $userId);

$ret = null;


switch ($sseMode) {
    case 'push':
		if ($this->httpRequest->hasInputValue('data')) {
			$data = $this->httpRequest->getInputValue('data');
		} else {
			$data = $this->httpRequest->getInputJSON();
		}
		if (!is_string($data)) {
			$data = json_encode($data);
		}
		//file_put_contents(__FILE__ . '.log', print_r($data, true));
        $sse->dispatchEvent($this->httpRequest->getInputValue('type'), $data);
        $this->httpResponse->setStatus(HTTPResponse::STATUS_NO_CONTENT);
        $this->progressStatus = self::STATUS_RESPONSE_SET;
        break;
    case 'pull':
        $ret = $sse->getStream();
        break;
    case 'last':
        break;
}

return $ret;