<?php

//*
use Slothsoft\CMS\Tracking\Manager;

$archive = Manager::getArchive();
$archive->install();
//$res = $archive->import();
if (!$this->httpRequest->getInputValue('parse', 1)) {
	$res = $archive->parse();
}

$view = Manager::getView();
$view->parseRequest($this->httpRequest);

return $view->asNode($dataDoc);