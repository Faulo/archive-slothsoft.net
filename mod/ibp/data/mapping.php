<?php
namespace VPlan;

$csvDir = __DIR__ . DIRECTORY_SEPARATOR . 'input-csv';
$mfdDir = __DIR__ . DIRECTORY_SEPARATOR . 'input-mfd';
$xsdDir = __DIR__ . DIRECTORY_SEPARATOR . 'input-xsd';

$retFragment = $dataDoc->createDocumentFragment();

$fileList = $this->httpRequest->getInputValue('file', []);
$mappingMatrix = [];
$mappingMatrix['IN'] = [];
$mappingMatrix['OUT'] = [];

$mfdFileList = \FileSystem::scanDir($mfdDir, \FileSystem::SCANDIR_REALPATH);
foreach ($mfdFileList as $mfdFile) {
	$name = basename($mfdFile);
	$fileNode = $dataDoc->createElement('mfdFile');
	$fileNode->setAttribute('name', $name);
	$retFragment->appendChild($fileNode);
	
	$file = new MFDFile($mfdFile);
	
	foreach ($fileList as $val) {
		if ($name === $val) {
			$mappingMatrix[$file->getMappingType()][] = $file->getMapping();
		}
	}
	
	if ($node = $file->asNode($dataDoc)) {
		$fileNode->appendChild($node);
	}
}

foreach ($mappingMatrix as $type => $mappingList) {
	if ($mappingList) {
		echo $type . PHP_EOL;
		foreach ($mappingList as $mapping) {
			foreach ($mapping as $key => $arr) {
				echo $key . PHP_EOL;
				foreach ($arr as $val) {
					echo "\t$val" . PHP_EOL;
				}
			}
			echo PHP_EOL;
		}
	}
}

return $retFragment;