<?php

$rootDir = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;

$retFragment = $dataDoc->createDocumentFragment();

$moduleList = \FileSystem::scanDir($rootDir, \FileSystem::SCANDIR_EXCLUDE_FILES);

foreach ($moduleList as $module) {
	$moduleDir = $rootDir . $module . DIRECTORY_SEPARATOR;
	$moduleNode = $dataDoc->createElement('module');
	$moduleNode->setAttribute('name', $module);
	if ($module === $this->httpRequest->getInputValue('module')) {
		$moduleNode->setAttribute('current', '');
		$fileList = \FileSystem::scanDir($rootDir . $module, \FileSystem::SCANDIR_EXCLUDE_DIRS);
		foreach ($fileList as $file) {
			if (preg_match('/^lang\..{5}\.xml$/', $file)) {
				$doc = \DOMHelper::loadDocument($moduleDir . $file);
				$moduleNode->appendChild($dataDoc->importNode($doc->documentElement, true));
			}
		}
	}
	$retFragment->appendChild($moduleNode);
}
return $retFragment;