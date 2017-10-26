<?php

$url = 'http://onepiece.wikia.com/wiki/Episode_Guide';

$sagaList = [];

$exceptionList = [];
$exceptionList[268] = "0268-0269 Catch Up with Luffy! The Straw Hat Pirates' All-Out War & Robin was Betrayed! The Expectations of the World Government!.mp4";
$exceptionList[277] = "0277-0278 Tragedy of Ohara! Fear of Buster Call! & Say You Want to Live! We are Friends!!.mp4";


if ($xpath = $this->loadExternalXPath($url, TIME_DAY)) {
	$tableNode = $xpath->evaluate('//*[@class="wikitable"][contains(., "East Blue Saga")][1]')->item(0);
	for ($sagaNo = 1; $sagaNo <= 6; $sagaNo++) {
		$arcNodeList = $xpath->evaluate(sprintf('*/*[%d]', $sagaNo), $tableNode);
		$sagaName = null;
		$arcList = [];
		foreach ($arcNodeList as $arcNo => $arcNode) {
			$name = $xpath->evaluate('normalize-space(.)', $arcNode);
			if ($sagaName === null) {
				$sagaName = sprintf('%02d %s', $sagaNo, $name);
			} else {
				if (preg_match('/(.+) \((\d+)-(\d+)\)/', $name, $match)) {
					$isFiller = $arcNode->hasAttribute('style');
					$arcName = sprintf('%02d %s', $arcNo, $match[1]);
					$arcList[$arcName] = [
						'min' => (int) $match[2],
						'max' => (int) $match[3],
						'filler' => $isFiller,
					];
				}
			}
		}
		$sagaList[$sagaName] = $arcList;
		//my_dump([$sagaName => $arcList]);
	}
}

$sourceDir = 'D:\backups\Media\Anime\One Piece 1-16 Seasons LIMITED';
$targetDir = 'D:\Media\Anime\One Piece';

$sourceFileList = [];
$dirList = \FileSystem::scanDir($sourceDir, \FileSystem::SCANDIR_REALPATH);
foreach ($dirList as $dir) {
	$fileList = \FileSystem::scanDir($dir, \FileSystem::SCANDIR_EXCLUDE_DIRS);
	foreach ($fileList as $file) {
		$file = mb_convert_encoding($file, 'UTF-8', 'Windows-1251');
		if (preg_match('/(\d+).+?([A-Z].+)/u', $file, $match)) {
			$no = (int) $match[1];
			$name = sprintf('%04d %s', $match[1], $match[2]);
			if (isset($exceptionList[$no])) {
				$name = $exceptionList[$no];
			}
			$name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
			$sourceFileList[$no] = [
				'source' => $dir . DIRECTORY_SEPARATOR . $file,
				'target' => $name,
			];
		}
	}
}

//my_dump($sourceFileList);die();
$copyList = [];

foreach ($sagaList as $sagaName => $arcList) {
	foreach ($arcList as $arcName => $arc) {
		$dir = $targetDir . DIRECTORY_SEPARATOR . $sagaName . DIRECTORY_SEPARATOR . $arcName . DIRECTORY_SEPARATOR;
		for ($i = $arc['min']; $i <= $arc['max']; $i++) {
			if (isset($sourceFileList[$i])) {
				$sourceFile = $sourceFileList[$i]['source'];
				$targetFile = $dir . $sourceFileList[$i]['target'];
				$copyList[$sourceFile] = $targetFile;
			} else {
				//my_dump($i);
			}
		}
	}
}

foreach ($copyList as $source => $target) {
	$source = mb_convert_encoding($source, 'Windows-1251', 'UTF-8');
	$target = mb_convert_encoding($target, 'Windows-1251', 'UTF-8');
	if (!is_dir(dirname($target))) {
		mkdir(dirname($target), 0777, true);
	}
	rename($source, $target);
}