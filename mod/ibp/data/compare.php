<?php
namespace VPlan;

$csvDir = __DIR__ . DIRECTORY_SEPARATOR . 'input-csv';
$mfdDir = __DIR__ . DIRECTORY_SEPARATOR . 'input-mfd';
$xsdDir = __DIR__ . DIRECTORY_SEPARATOR . 'input-xsd';

$retFragment = $dataDoc->createDocumentFragment();

$csvFileList = \FileSystem::scanDir($csvDir, \FileSystem::SCANDIR_REALPATH);
foreach ($csvFileList as $csvFile) {
	$name = basename($csvFile);
	$fileNode = $dataDoc->createElement('csvFile');
	$fileNode->setAttribute('name', $name);
	$retFragment->appendChild($fileNode);
	
	$file = new CSVFile($csvFile);
	
	if ($this->httpRequest->getInputValue('file') === $name) {
		$doc = new \DOMDocument();
		$doc->appendChild($file->asNode($doc));
		return \CMS\HTTPFile::createFromDocument($doc);
	}
	
	if ($node = $file->asNode($dataDoc)) {
		$fileNode->appendChild($node);
	}
}


$mfdFileList = \FileSystem::scanDir($mfdDir, \FileSystem::SCANDIR_REALPATH);
foreach ($mfdFileList as $mfdFile) {
	$name = basename($mfdFile);
	$fileNode = $dataDoc->createElement('mfdFile');
	$fileNode->setAttribute('name', $name);
	$retFragment->appendChild($fileNode);
	
	$file = new MFDFile($mfdFile);
	
	if ($this->httpRequest->getInputValue('file') === $name) {
		$doc = new \DOMDocument();
		$doc->appendChild($file->asNode($doc));
		return \CMS\HTTPFile::createFromDocument($doc);
	}
	
	if ($node = $file->asNode($dataDoc)) {
		$fileNode->appendChild($node);
	}
}


$fileList = \FileSystem::scanDir($xsdDir, \FileSystem::SCANDIR_REALPATH);
foreach ($fileList as $file) {
	$name = basename($file);
	$fileNode = $dataDoc->createElement('xsdFile');
	$fileNode->setAttribute('name', $name);
	$retFragment->appendChild($fileNode);
	
	$file = new XSDFile($file);
	
	if ($this->httpRequest->getInputValue('file') === $name) {
		$doc = new \DOMDocument();
		$doc->appendChild($file->asNode($doc));
		return \CMS\HTTPFile::createFromDocument($doc);
	}
	
	if ($node = $file->asNode($dataDoc)) {
		$fileNode->appendChild($node);
	}
}
if (XSDFile::$dump) {
	my_dump(XSDFile::$dump);
}
return $retFragment;