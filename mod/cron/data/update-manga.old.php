<?php
$ret = '';
$ret .= sprintf('[%s] Starting updating...%s%s', date(DATE_DATETIME), PHP_EOL, PHP_EOL);

$doc = $this->getResourceDoc('cron/update-manga', 'xml');
$xpath = self::loadXPath($doc);

$targetRoot = realpath('D:/NetzwerkDaten/Manga');
$targetDirExpr = '%s Chapter %04d';
$targetFileExpr = '%03d.%s';
$minDataLength = 100;
$maxChapterCount = 1000;
set_time_limit(0);

if ($targetRoot) {
	$updateNodeList = $xpath->evaluate('//manga');
	foreach ($updateNodeList as $updateNode) {
		$name = $updateNode->getAttribute('name');
		$sourceHost = $updateNode->getAttribute('source-host');
		$sourcePath = $updateNode->getAttribute('source-path');
		
		$targetPath = $targetRoot . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
		
		if (!is_dir($targetPath)) {
			mkdir($targetPath);
		}
		
		$startChapter = 1;
		$chapterList = \FileSystem::scanDir($targetPath, \FileSystem::SCANDIR_EXCLUDE_FILES);
		foreach ($chapterList as $chapter) {
			if (preg_match('/(\d+)/', $chapter, $match)) {
				$no = (int) $match[1];
				if ($no > $startChapter) {
					$startChapter = $no;
				}
			}
		}
		//$startChapter = 1;
		
		$ret .= sprintf('[%s] Updating "%s":%s', date(DATE_DATETIME), $name, PHP_EOL);
		$ret .= sprintf('	Looking for chapter #%d...%s', $startChapter, PHP_EOL);
		
		$chapterNo = $startChapter;
		$pageNo = 1;
		$chapterCount = 0;
		$fallbackCount = 0;
		$lastImg = null;
		$lastData = null;
		do {
			$continue = false;
			$advanceChapter = false;
			$uri = sprintf($sourcePath, $chapterNo, $pageNo);
			if ($xpath = self::loadExternalXPath($sourceHost . $uri, 0)) {
				$nodeList = $xpath->evaluate('.//html:img[@id="img" or @width="100%"] | .//div[@id="page"]//img[@class="open"]');
				if ($node = $nodeList->item(0)) {
					//my_dump($doc->saveXML($node));
					if ($pageNo === 1) {
						if ($startChapter === $chapterNo) {
							$ret .= sprintf('	Verifying chapter #%d is complete... %s%s', $chapterNo, $sourceHost . $uri, PHP_EOL);
						} else {
							$ret .= sprintf('	Found chapter #%d! Downloading... %s%s', $chapterNo, $sourceHost . $uri, PHP_EOL);
						}
					}
					
					
					$img = $xpath->evaluate('string(@src)', $node);
					
					if ($img === $lastImg) {
						$advanceChapter = true;
					} else {
						$lastImg = $img;
					
						$ext = explode('.', $img);
						$ext = array_pop($ext);
						
						$targetDir = sprintf($targetDirExpr, $name, $chapterNo) . DIRECTORY_SEPARATOR;
						$targetFile = sprintf($targetFileExpr, $pageNo, $ext);
						if (!is_dir($targetPath . $targetDir)) {
							mkdir($targetPath . $targetDir);
						}
						$target = $targetPath . $targetDir. $targetFile;
						//$req = new \XMLHttpRequest();
						//$req->open('GET', $img);
						//$req->send();
						//$data = $req->responseText;
						
						$pageNo++;
						if (file_exists($target)) {
							//$ret .= sprintf('		Skipping %s%s', $targetFile, PHP_EOL);
							$continue = true;
						} else {
							//$ret .= sprintf('		Downloading %s...%s', $targetFile, PHP_EOL);
							$data = file_get_contents($img);
							
							if ($data === $lastData) {
								$advanceChapter = true;
							} else {
								$lastData = $data;
								if (strlen($data) > $minDataLength) {
									file_put_contents($target, $data);
									$continue = true;
								} else {
									$ret .= sprintf('		ERROR downloading %s! °A°%s', $img, PHP_EOL);
								}
							}
						}
					}
				} else {
					$ret .= sprintf('	Chapter #%d not found, aborting!%s', $chapterNo, PHP_EOL);
				}
			} else {
				$advanceChapter = true;
			}
			if ($advanceChapter) {
				if ($pageNo === 1) {
					$ret .= sprintf('	Chapter #%d not found, aborting!%s', $chapterNo, PHP_EOL);
					$advanceChapter = $chapterNo === $startChapter;
				}
				if ($advanceChapter) {
					$continue = true;
					$pageNo = 1;
					$chapterNo++;
					$chapterCount++;					
					$lastImg = null;
					$lastData = null;
					if ($chapterCount === $maxChapterCount) {
						$ret .= sprintf('	Downloaded %d chapters, stopping for now...%s', $maxChapterCount, PHP_EOL);
						$continue = false;
					}
				}
			}
			if ($fallbackCount++ > $maxChapterCount * 100) {
				$ret .= sprintf('	FallbackCount reached! oO Last request was chapter %d, page %d...%s', $chapterNo, $pageNo, PHP_EOL);
				$continue = false;
			}
		} while ($continue);
		$ret .= PHP_EOL;
	}
}

$ret .= sprintf('[%s] ...done! \\o/', date(DATE_DATETIME));


$this->progressStatus |= self::STATUS_RESPONSE_SET;
$this->httpResponse->setStatus(\CMS\HTTPResponse::STATUS_OK);
$this->httpResponse->setBody($ret);
$this->httpResponse->setEtag(\CMS\HTTPResponse::calcEtag($ret));