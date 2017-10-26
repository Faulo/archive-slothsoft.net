<?php
if (!$this->httpRequest->getInputValue('update')) {
	//echo 'DEACTIVATED'; return;
}

$uri = 'https://minecraft.net/en-us/download/server';
$downloadHost = 'https://s3.amazonaws.com/Minecraft.Download/versions/';
$ext = '.jar';
$targetList = [
	'C:/NetzwerkDaten/Dropbox/MinecraftServer',
	'C:/NetzwerkDaten/Dropbox/Dani Julia/Minecraft',
	//'C:/NetzwerkDaten/Dropbox/MinecraftServer/MCEdit/ServerJarStorage/latest',
];


$ret = 'Starting update... ' . $uri . PHP_EOL;
if ($xpath = \Storage::loadExternalXPath($uri, 0)) {
	$nodeList = $xpath->evaluate('.//*[contains(@href, "minecraft_server")]');
	foreach ($nodeList as $node) {
		$uri = trim($node->getAttribute('href'));
		$targetFile = 'minecraft_server.jar';
		
		if (strpos($uri, $downloadHost) === 0 and substr($uri, - strlen($ext)) === $ext) {
			if ($file = \CMS\HTTPFile::createFromURL($uri, $targetFile)) {
				$ret .= $uri . PHP_EOL;
				foreach ($targetList as $target) {
					if ($targetDir = realpath($target)) {
						$targetDir .= DIRECTORY_SEPARATOR;
						$target = $targetDir . $targetFile;
						if (!file_exists($target)) {
							file_put_contents($target, '');
						}
						$target = realpath($target);
						$ret .= $target . PHP_EOL;
						if (file_get_contents($target) !== $file->getContents()) {
							if ($file->copyTo($targetDir, $targetFile)) {
								$ret .= 'UPDATED! n__n/' . PHP_EOL;
							} else {
								$ret .= 'ERROR °A°' . PHP_EOL;
							}
						} else {
							$ret .= 'CURRENT! \\n__n' . PHP_EOL;
						}
					} else {
						$ret .= 'TARGET DIR NOT FOUND ?? *A* ' . PHP_EOL . $target . PHP_EOL;
					}
				}
				break;
			}
		}
	}
}
$this->progressStatus |= self::STATUS_RESPONSE_SET;
$this->httpResponse->setStatus(\CMS\HTTPResponse::STATUS_OK);
$this->httpResponse->setBody($ret);
$this->httpResponse->setEtag(\CMS\HTTPResponse::calcEtag($ret));
return;












$baseHost = 'https://mojang.com';
//$uri = 'https://minecraft.net/download';
$downloadHost = 'https://s3.amazonaws.com/Minecraft.Download/versions/';
$ext = '.jar';
$targetList = [
	'C:/NetzwerkDaten/Dropbox/MinecraftServer',
	'C:/NetzwerkDaten/Dropbox/Dani Julia/Minecraft',
	//'C:/NetzwerkDaten/Dropbox/MinecraftServer/MCEdit/ServerJarStorage/latest',
];


//$doc = new \DOMDocument();
//$doc->loadHTMLFile($uri);
$ret = 'Starting update... ' . $baseHost . PHP_EOL;

$baseXPath = \Storage::loadExternalXPath($baseHost, 0);
$h1NodeList = $baseXPath->evaluate('.//a[h1]');
foreach ($h1NodeList as $h1Node) {
	$uri = $baseHost . $h1Node->getAttribute('href');
	
	$xpath = \Storage::loadExternalXPath($uri, 0);
	//my_dump($xpath->document->saveXML());
	//$nodeList = $xpath->evaluate('.//*[@href]');
	$nodeList = $xpath->evaluate('.//*[contains(@href, "minecraft_server")]');
	foreach ($nodeList as $node) {
		$uri = trim($node->getAttribute('href'));
		$targetFile = 'minecraft_server.jar';
		
		if (strpos($uri, $downloadHost) === 0 and substr($uri, - strlen($ext)) === $ext) {
			if ($file = \CMS\HTTPFile::createFromURL($uri, $targetFile)) {
				$ret .= $uri . PHP_EOL;
				foreach ($targetList as $target) {
					if ($targetDir = realpath($target)) {
						$targetDir .= DIRECTORY_SEPARATOR;
						$target = $targetDir . $targetFile;
						if (!file_exists($target)) {
							file_put_contents($target, '');
						}
						$target = realpath($target);
						$ret .= $target . PHP_EOL;
						if (file_get_contents($target) !== $file->getContents()) {
							if ($file->copyTo($targetDir, $targetFile)) {
								$ret .= 'UPDATED! n__n/' . PHP_EOL;
							} else {
								$ret .= 'ERROR °A°' . PHP_EOL;
							}
						} else {
							$ret .= 'CURRENT! \\n__n' . PHP_EOL;
						}
					} else {
						$ret .= 'TARGET DIR NOT FOUND ?? *A* ' . PHP_EOL . $target . PHP_EOL;
					}
				}
				break 2;
			}
			/*
			if ($data = file_get_contents($uri)) {
				$ret = $uri . PHP_EOL;
				foreach ($targetList as $target) {
					if ($target = realpath($target)) {
						$target .= '/minecraft_server.jar';
						if (!file_exists($target)) {
							file_put_contents($target, '');
						}
						$target = realpath($target);
						$ret .= $target . PHP_EOL;
						if (file_get_contents($target) !== $data) {
							file_put_contents($target, $data);
							$ret .= 'UPDATED! n__n/' . PHP_EOL;
						} else {
							$ret .= 'CURRENT! \\n__n' . PHP_EOL;
						}
					}
				}
				$this->progressStatus |= self::STATUS_RESPONSE_SET;
				$this->httpResponse->setStatus(\CMS\HTTPResponse::STATUS_OK);
				$this->httpResponse->setBody($ret);
				$this->httpResponse->setEtag(\CMS\HTTPResponse::calcEtag($ret));
				return;
			}
			//*/
		}
	}
}

$this->progressStatus |= self::STATUS_RESPONSE_SET;
$this->httpResponse->setStatus(\CMS\HTTPResponse::STATUS_OK);
$this->httpResponse->setBody($ret);
$this->httpResponse->setEtag(\CMS\HTTPResponse::calcEtag($ret));
return;