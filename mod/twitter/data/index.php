<?php

$retFragment = $dataDoc->createDocumentFragment();

$options = [];
$options['limit'] = $this->httpRequest->getInputValue('limit', 256);
$options['offset'] = $this->httpRequest->getInputValue('start', -1);
$options['pics'] = (int) $this->httpRequest->getInputValue('pics', -1);

$userList = \Twitter\Archive::getUserList();
foreach ($userList as $user) {
	$archive = new \Twitter\Archive($user);
	//$archive->upgrade();
	$retFragment->appendChild($archive->asNode($dataDoc, $options));
}

return $retFragment;