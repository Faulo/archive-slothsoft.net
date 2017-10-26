<?php
$torrentPath = '"C:/Program Files (x86)/BitTorrent/BitTorrent.exe" "%s"';
$torrentBatch = 'C:/NetzwerkDaten/Dropbox/Tools/createTorrent.bat';

$uploadKey = 'uploads';

$downloadDirsList = [
	0 => [
		'C:/NetzwerkDaten/Uploads/'
	],
	'pr0n' => [
		'C:/NetzwerkDaten/pr0n/videos/',
	],
	'files' => [
		'C:/NetzwerkDaten/YouTube/',
		'C:/NetzwerkDaten/Uploads/',
		'C:/NetzwerkDaten/Downloads/',
		'C:/NetzwerkDaten/Torrents/',
	],
	'misc' => [
		'C:/NetzwerkDaten/Uploads/',
		'C:/NetzwerkDaten/Downloads/',
		'C:/NetzwerkDaten/Torrents/',
		'D:/backups/Media/',
		'D:/Media/',
	],
	'media' => [
		'D:/Media/',
	],
	'backup' => [
		'D:/backups/Media/',
	],
	'tales' => [
		'D:/Media/Anime/Tales/',
	],
	'ghibli' => [
		'D:/Media/Movies/Studio Ghibli/',
		//'C:/NetzwerkDaten/Series/Movies/Studio Ghibli/',
	],
];

$downloadDirs = $downloadDirsList[0];

if ($archive = $this->httpRequest->getInputValue('load-archive') and isset($downloadDirsList[$archive])) {
	$downloadDirs = $downloadDirsList[$archive];
}
$tmpList = [];
foreach ($downloadDirs as $dir) {
	if ($dir = realpath($dir)) {
		$key = \FileSystem::generateStorageKey($dir);
		$tmpList[$key] = $dir;
	}
}
$downloadDirs = $tmpList;

$uploadDir = reset($downloadDirs);

$verifyTree = !($this->httpRequest->getInputValue('watch') or $this->httpRequest->getInputValue('subtitle') or $this->httpRequest->getInputValue('download') or $this->httpRequest->getInputValue('show'));

if ($uri = $this->httpRequest->getInputValue('youtube-uri')) {
	$dir = 'C:/NetzwerkDaten/YouTube/';
	if (chdir($dir)) {
		
		$async = true;
		
		if ($async) {
			$cmd = sprintf('start youtube-dl %s', escapeshellarg($uri));
			pclose(popen($cmd, 'r'));
		} else {
			$cmd = sprintf('youtube-dl --print-json %s', escapeshellarg($uri));
			$output = [];
			exec($cmd, $output);
			$data = implode(PHP_EOL, $output);
			$data = json_decode($data, true);
			if (!isset($data['_filename']) or !realpath($data['_filename'])) {
				my_dump($output);
				my_dump($data);
				//return \CMS\HTTPFile::createFromString(implode(PHP_EOL, array_merge([$cmd], $output)));
				$cmd = sprintf('youtube-dl %s', escapeshellarg($uri));
				echo $cmd . PHP_EOL . PHP_EOL;
				passthru($cmd);
				die();
			}
		}
		
		/*
		$oldPath = realpath($data['_filename']);
		//$oldPath = $data['_filename'];
		$ext = pathinfo($oldPath, PATHINFO_EXTENSION);
		$newPath = substr($oldPath, 0, - strlen($ext)) . 'webm';
		if ($oldPath !== $newPath) {
			$cmd = sprintf(
				'ffmpeg -i %s -c:v libvpx -quality good -cpu-used 0 -crf 10 -qmin 0 -qmax 48 -b:v 1M -c:a libvorbis -q:a 8 -threads 4 -f webm -y %s',
				escapeshellarg($oldPath), escapeshellarg($newPath)
			);
			$output = [];
			exec($cmd, $output);
			
			if (!file_exists($newPath)) {
				return \CMS\HTTPFile::createFromString(implode(PHP_EOL, array_merge([$cmd], $output)));
			}
		}
		//*/
		$verifyTree = true;
	}
}

//$verifyTree = true;
//my_dump($verifyTree);die();
//http://www.loc.gov/standards/iso639-2/php/English_list.php
/*
if ($torrent = $this->httpRequest->getInputValue('torrent')) {
	$exec = sprintf($torrentPath, $torrent);
	$execList = file($torrentBatch);
	if (!in_array($exec, $execList)) {
		$execList[] = $exec;
	}
	file_put_contents($torrentBatch, implode(PHP_EOL, $execList));
}
//*/

if (isset($_FILES[$uploadKey])) {
	foreach ($_FILES[$uploadKey]['error'] as $i => $error) {
		if ($error === 0) {
			//my_dump($_FILES);
			$success = move_uploaded_file($_FILES[$uploadKey]['tmp_name'][$i], $uploadDir . DIRECTORY_SEPARATOR . utf8_decode(basename($_FILES[$uploadKey]['name'][$i])));
			//my_dump( $defaultDir . $_FILES[$uploadKey]['name'][$i]);
			//my_dump(file_exists( $defaultDir . $_FILES[$uploadKey]['name'][$i]));
		}
	}
}

$retNode = $dataDoc->createDocumentFragment();
$dataRoot = $dataDoc->documentElement;
$dataPath = new \DOMXPath($dataDoc);
$storage = \FileSystem::getStorage();

foreach ($downloadDirs as $key => $dir) {
	$newNode = null;
	if ($verifyTree) {
		$newNode = \FileSystem::asNode($dir, $dataDoc);
	} else {
		$newNode = $storage->retrieveXML($key, 0, $dataDoc);
	}
	if ($newNode) {
		$retNode->appendChild($newNode);
	}
}

/*
$expr = './/file[@name = "Thumbs.db"]';
$nodeList = $dataPath->evaluate($expr, $retNode);
foreach ($nodeList as $node) {
	@unlink($node->getAttribute('path'));
	touch(dirname($node->getAttribute('path')));
}
$expr = './/file[@name = "Thumbs.db"] | .//file[@ext = "srt"][@size-int = "0"]';
$nodeList = $dataPath->evaluate($expr, $retNode);
foreach ($nodeList as $node) {
	@unlink($node->getAttribute('path'));
	touch(dirname($node->getAttribute('path')));
}
//*/

if (isset($this->httpRequest->input['convert'])) {
	$convert = '';
	$expr = sprintf('.//folder[@name = "%s"]/descendant-or-self::folder[file[@isVideo]]', $convert);
	$expr = 'descendant-or-self::folder[file[@isVideo]]';
	$nodeList = $dataPath->evaluate($expr, $retNode);
	$createSubList = [];
	foreach ($nodeList as $folderNode) {
		$oldDir = $folderNode->getAttribute('path');
		$newDir = str_replace('\\backups\\', '\\', $oldDir);
		if (!file_exists($newDir)) {
			continue;
		}
		$oldIndex = $oldDir . DIRECTORY_SEPARATOR . '_index.txt';
		$newIndex = $newDir . DIRECTORY_SEPARATOR . '_index.txt';
		if (file_exists($oldIndex) and !file_exists($newIndex)) {
			copy($oldIndex, $newIndex);
		}
		
		$childList = $dataPath->evaluate('file[@isVideo]', $folderNode);
		$createVidList = [];
		foreach ($childList as $i => $child) {
			$oldPath = $child->getAttribute('path');
			$ext = $child->getAttribute('ext');
			/*
			if ($ext !== 'ogv') {
				$newPath = substr($oldPath, 0, - strlen($ext)) . 'ogv';
				if (!file_exists($newPath)) {
					$createVidList[] = sprintf('start /low createOGV "%s" "%s"', $oldPath, $newPath);
				}
			}
			//*/
			if ($ext !== 'webm' and $ext !== 'ogv') {
				$mediaInfo = \FileSystem::mediaInfo($oldPath);
				
				//webm
				$newPath = substr($oldPath, 0, - strlen($ext)) . 'webm';
				$newPath = str_replace('\\backups\\', '\\', $newPath);
				if (!file_exists($newPath)) {
					$mappingList = [];
					foreach (['video', 'audio'] as $type) {
						/*
						$streamList = $dataPath->evaluate(sprintf('mediaInfo/stream[@type = "%s"]', $type), $child);
						$mapping = [];
						$languages = [];
						foreach ($streamList as $streamNode) {
							$key = $streamNode->getAttribute('key');
							$lang = $streamNode->getAttribute('lang');
							$langInfo = \Lang\Dictionary::languageInfo($lang);
							$lang = $langInfo['code'];
							$mapping[$key] = '-map ' . $key;
							$languages[$key] = $lang;
						}
						//*/
						$streamList = $mediaInfo[$type];
						$mapping = [];
						$languages = [];
						foreach ($streamList as $key => $stream) {
							$langInfo = \Lang\Dictionary::languageInfo($stream['lang']);
							$mapping[$key] = '-map ' . $key;
							$languages[$key] = $langInfo['code'];
						}
						if (!$mapping) {
							echo 'No streams?? o_O' . PHP_EOL;
							my_dump($mediaInfo);
							continue 2;
						}
						if ($sort = $this->httpRequest->getInputValue('sort')) {
							$sortList = explode('-', $sort);
							$tmpLanguages = $languages;
							$newLanguages = [];
							$newMapping = [];
							foreach ($sortList as $lang) {
								$keyList = array_keys($tmpLanguages, $lang, true);
								foreach ($keyList as $key) {
									$newMapping[$key] = $mapping[$key];
									unset($tmpLanguages[$key]);
								}
							}
							foreach ($tmpLanguages as $key => $lang) {
								$newMapping[$key] = $mapping[$key];
							}
							foreach ($newMapping as $key => $tmp) {
								$newLanguages[$key] = $languages[$key];
							}
							$mapping = $newMapping;
							$languages = $newLanguages;
						}
						if ($limit = (int) $this->httpRequest->getInputValue('limit')) {
							$mapping = array_slice($mapping, 0, $limit);
						}
						$mappingList[] = implode(' ', $mapping);
					}
					$mappingList = implode('  ', $mappingList);
					$createVidList[] = sprintf('start /low /wait createWebM "%s" "%s" "%s"', $oldPath, $newPath, $mappingList);
				}
				
				//srt
				$streamList = $mediaInfo['subtitle'];
				$i = 1;
				foreach ($streamList as $key => $stream) {
					$mapping = '-map ' . $key;
					$langInfo = \Lang\Dictionary::languageInfo($stream['lang']);
					$newPath = sprintf(
						'%s%02d.%s.srt',
						substr($oldPath, 0, - strlen($ext)),
						$i,
						$langInfo['code']
					);
					$newPath = str_replace('\\backups\\', '\\', $newPath);
					if (!file_exists($newPath)) {
						$createSubList[] = sprintf('start /low /wait createSRT "%s" "%s" "%s"', $oldPath, $newPath, $mapping);
					}
					$i++;
				}		
			}
		}
		if ($createVidList) {
			echo implode(PHP_EOL, $createVidList) . PHP_EOL . PHP_EOL;
		}		
	}
	if ($createSubList) {
		echo PHP_EOL . PHP_EOL . implode(PHP_EOL, $createSubList);
	}
}

if (isset($this->httpRequest->input['rename'])) {
	// grrrrrrrrr
	$expr = sprintf('.//folder//file[@name = "_index.txt.ccc"]');
	$nodeList = $dataPath->evaluate($expr, $retNode);
	foreach ($nodeList as $node) {
		$path = $node->getAttribute('path');
		file_put_contents(substr($path, 0, -4), '');
		unlink($path);
	}
	
	$renameCount = (int) $this->httpRequest->getInputValue('rename', 0);
	$expr = sprintf('.//folder//file[@name = "_index.txt"]');
	$newNameExpr = '%s%s%s%s - %s.%s';
	$nodeList = $dataPath->evaluate($expr, $retNode);
	foreach ($nodeList as $node) {
		$folderNode = $node->parentNode;
		$nameList = file($node->getAttribute('path'), FILE_IGNORE_NEW_LINES);
		$fileList = [];
		$fileListCount = 0;
		$season = $folderNode->getAttribute('name');
		$folderPath = $folderNode->getAttribute('path');
		if (preg_match('/(\d+)/', $season, $match)) {
			$season = sprintf('%02dx', (int) $match[1]);
		} else {
			$season = '';
		}
		$childList = $dataPath->evaluate('file[@isVideo or @isAudio]', $folderNode);
		if (!$nameList) {
			foreach ($childList as $i => $child) {
				if (preg_match('/([A-Z].*)\./', $child->getAttribute('name'), $match)) {
					$nameList[$i] = $match[1];
				} else {
					//die($child->getAttribute('path'));
					$nameList[$i] = '???';
				}
			}
			file_put_contents($node->getAttribute('path'), implode(PHP_EOL, $nameList));
		}
		$lastArr = null;
		$reduceCount = 0;
		foreach ($nameList as $i => &$arr) {
			$i -= $reduceCount;
			$arr = [
				'start' => $i + 1,
				'end' => $i + 1,
				'name' => FileSystem::filenameSanitize($arr),
				'append' => '',
			];
			if (strlen($arr['name'])) {
				if (preg_match('/^(\.\d+)\s(.+)$/', $arr['name'], $match)) {
					$arr['append'] = $match[1];
					$arr['name'] = $match[2];
					$arr['start']--;
					$arr['end']--;
					$reduceCount++;
				}
				unset($lastArr);
				$lastArr = &$arr;
			} else {
				if ($lastArr) {
					$lastArr['end'] = $i + 1;
				}
				$arr = null;
			}
		}
		unset($arr);
		$nameList = array_values(array_filter($nameList));
		
		$sprintf = count($nameList) > 99
			? '%s%03d%s'
			: '%s%02d%s';
		
		foreach ($childList as $i => $child) {
			if (isset($nameList[$i])) {
				$name = $nameList[$i]['name'];
				$noAppend = $nameList[$i]['append'];
				$noStart = sprintf($sprintf, $season, $nameList[$i]['start'], $noAppend);
				$noEnd = $nameList[$i]['start'] === $nameList[$i]['end']
					? ''
					: '-'.sprintf($sprintf, $season, $nameList[$i]['end'], $noAppend);
				$ext = strtolower($child->getAttribute('ext'));
				$oldName = $child->getAttribute('path');
				$newName = sprintf(
					$newNameExpr,
					$folderPath,
					DIRECTORY_SEPARATOR,
					$noStart,
					$noEnd,
					$name,
					$ext
				);
				if ($oldName !== $newName and !file_exists($newName)) {
					$fileList[$oldName] = $newName;
					$oldName = substr($oldName, 0, -4) . '.srt';
					$newName = substr($newName, 0, -4) . '.srt';
					if (file_exists($oldName) and !file_exists($newName)) {
						$fileList[$oldName] = $newName;
					}
				}
				$fileListCount++;
			} else {
				echo sprintf('cannot rename #%d "%s"~%s', $i, $child->getAttribute('path'), PHP_EOL);
				my_dump($nameList);
				continue 2;
				die('Stopping the renaming!! :D');
			}
		}
		if ($fileList) {
			my_dump($fileList);
			if ($fileListCount === $childList->length) {	// or $renameCount > 0
				if ($this->httpRequest->getInputValue('rename')) {
					foreach ($fileList as $oldName => $newName) {
						rename($oldName, $newName);
						touch($newName);
					}
				}
				$renameCount--;
			} else {
				echo 'will not rename, nameList lengths do not match! D:' . PHP_EOL;
				foreach ($childList as $node) {
					echo $node->getAttribute('path').PHP_EOL;
				}
			}
		}
	}
}

$mode = 'toc';

$requestNodes = [
	'download' => null,
	'show' => null,
	'subtitles' => null,
	'watch' => null,
	'info' => null,
	'nico' => null,
];
foreach ($requestNodes as $key => &$node) {
	if ($id = $this->httpRequest->getInputValue($key)) {
		$query = sprintf('.//*[@id = "%s"]', $id);
		//my_dump($query);
		$nodeList = $dataPath->evaluate(
			$query,
			$retNode
		);
		$node = $nodeList->item(0);
		$mode = $key;
		if (!$node) {
			my_dump($query);
			echo $dataPath->document->saveXML($retNode);
			$this->httpResponse->setStatus(\CMS\HTTPResponse::STATUS_NOT_FOUND);
			$this->progressStatus = self::STATUS_RESPONSE_SET;
			$retNode = null;
		}
	}
}
unset($node);

$ret = $retNode;

if ($node = $requestNodes['subtitles']) {
	$title = $node->getAttribute('title');
	$filePath = $node->getAttribute('path');
	$subtitlePath = null;
	$nodeList = $dataPath->evaluate(
		sprintf('../file[@title = "%s"][@isSubttitle]', $title),
		$node
	);
	if ($node = $nodeList->item(0)) {
		$subtitlePath = $node->getAttribute('path');
	} else {
		$mediaInfo = \FileSystem::mediaInfo($filePath);
		if ($mediaInfo['subtitle']) {
			$subtitlePath = $filePath;
		}
	}
	
	if ($subtitlePath) {
		$tmpPath = tempnam(sys_get_temp_dir(), 'archive');
		$tmpName = $title . '.vtt';
		$command = sprintf('%s -i "%s" -f webvtt "%s" -y', \FileSystem::FFMPEG_PATH, $subtitlePath, $tmpPath);
		exec($command, $res);
		//echo $command . PHP_EOL . filesize($tmpPath) . PHP_EOL;
		//die(file_get_contents($tmpPath));
		$ret = \CMS\HTTPFile::createFromPath($tmpPath, $tmpName);
	} else {
		$this->httpResponse->setStatus(\CMS\HTTPResponse::STATUS_NOT_FOUND);
		$this->progressStatus = self::STATUS_RESPONSE_SET;
		$ret = null;
	}
}
if ($node = $requestNodes['download']) {
	$path = $node->getAttribute('path');
	$name = $node->getAttribute('name');
	$path = utf8_decode($path);
	$this->httpResponse->setDownload(true);
	$ret = \CMS\HTTPFile::createFromPath($path, $name);
}
if ($node = $requestNodes['show']) {
	$path = $node->getAttribute('path');
	$name = $node->getAttribute('name');
	$path = utf8_decode($path);
	$this->httpResponse->setDownload(false);
	$ret = \CMS\HTTPFile::createFromPath($path, $name);
}
if ($node = $requestNodes['watch']) {
	$node->setAttribute('current', '');
}
if ($node = $requestNodes['info']) {
	//my_dump(\FileSystem::mediaInfo($node->getAttribute('path')));
	$dataDoc->replaceChild($node, $dataDoc->documentElement);
	$ret = \CMS\HTTPFile::createFromDocument($dataDoc, 'info.xml');
}
//NICO NICO
if ($node = $requestNodes['nico']) {
	$videoNode = $dataPath->evaluate('file[@ext="webm"]', $node)->item(0);
	$fileNode = $dataPath->evaluate('file[@ext="flv"]', $node)->item(0);
	$commentNodeList = $dataPath->evaluate('file[@ext="xml"]', $node);
	
	if (!$requestNodes['download']) {
		$doc = new \DOMDocument();
		$rootNode = $doc->createElement('nico');
		$doc->appendChild($rootNode);
		foreach ($commentNodeList as $commentNode) {
			$path = $commentNode->getAttribute('path');
			$tmpDoc = self::loadDocument($path);
			$rootNode->appendChild($doc->importNode($tmpDoc->documentElement, true));
		}
		if ($videoNode) {
			$videoNode = $doc->importNode($videoNode, true);
			$videoNode->setAttribute('_video', '');
			$rootNode->appendChild($videoNode);
		}
		if ($fileNode) {
			$fileNode = $doc->importNode($fileNode, true);
			$fileNode->setAttribute('_file', '');
			$rootNode->appendChild($fileNode);
		}
		$xpath = self::loadXPath($doc);
		$dom = new \DOMHelper();
		$rootTime = $xpath->evaluate('number(//@server-keytime)');
		$videoTime = $xpath->evaluate('string(//@video-keytime)');
		$commentsTime = $xpath->evaluate('string(//@comments-keytime)');
		if ($commentsTime and $videoTime) {
			$commentsTime = strtotime("1970-01-01 00:$commentsTime UTC");
			$videoTime = strtotime("1970-01-01 00:$videoTime UTC");
			
			$rootTime += $commentsTime;
			$rootTime -= $videoTime;
		}
		
		$nodeList = $doc->getElementsByTagName('chat');
		$commentList = [];
		$deleteList = [];
		$htmlNodeList = [];
		$htmlCode = '';
		foreach ($nodeList as $node) {
			$key = $node->getAttribute('date') . ':' . $node->getAttribute('user_id');
			
			if (isset($commentList[$key])) {
				$deleteList[] = $node;
			} else {
				$commentList[$key] = true;
			
				$time = $node->getAttribute('date_usec');
				$time = (float) $time;
				$time /= 1000000;
				$time += (float) $node->getAttribute('date');
				
				$node->setAttribute('date_string', gmdate(DATE_DATETIME, $time));
				
				$time -= $rootTime;
				$node->setAttribute('time', $time);
				$node->setAttribute('time_string', gmdate(DATE_TIME, $time > 0 ? $time : 0));
				
				$html = $node->textContent;
				
				/*
				$html = str_replace('<br>', '<br/>', $html);
				$html = str_replace('&', '&amp;', $html);
				$html = preg_replace('/\<([^\/a-z])/', '&lt;$1', $html);
				$html = preg_replace('/([^\/a-z"])\>/', '$1&gt;', $html);
				//*/
				
				$html = sprintf('<p>%s</p>', $html);
				$htmlCode .= $html;
				$htmlNodeList[] = $node;
				
				/*
				$html = sprintf('<p xmlns="%s">%s</p>', \DOMHelper::NS_HTML, $html);
				$htmlNode = $dom->parse($html, $doc, true);
				$html = $doc->saveXML($htmlNode);
				$html = str_replace(' xmlns=""', '', $html);
				$htmlNode = $dom->parse($html, $doc, false);
				$node->replaceChild($htmlNode, $node->firstChild);
				//*/
			}
		}
		$htmlCode = sprintf('<div xmlns="%s">%s</div>', \DOMHelper::NS_HTML, $htmlCode);
		if ($htmlCodeNode = $dom->parse($htmlCode, $doc, true)) {
			if ($htmlCodeNode = $htmlCodeNode->removeChild($htmlCodeNode->firstChild)) {
				$htmlCode = $doc->saveXML($htmlCodeNode);
				$htmlCode = str_replace(' xmlns=""', '', $htmlCode);
				if ($htmlCodeNode = $dom->parse($htmlCode, $doc, false)) {
					if ($htmlCodeNode = $htmlCodeNode->removeChild($htmlCodeNode->firstChild)) {
						$nodeList = [];
						foreach ($htmlCodeNode->childNodes as $node) {
							$nodeList[] = $node;
						}
						foreach ($htmlNodeList as $i => $node) {
							$node->replaceChild($nodeList[$i], $node->firstChild);
						}
					}
				}
			}
		}
		foreach ($deleteList as $node) {
			$node->parentNode->removeChild($node);
		}
	
		$doc = $dom->transform(
			$doc,
			$this->getTemplateDoc('slothsoft/_archive.nico')
		);
		$ret = \CMS\HTTPFile::createFromDocument($doc, 'nico-comments.xhtml');
	}
}
if (isset($this->httpRequest->input['host'])) {
	$mode = 'host';
}
if (isset($this->httpRequest->input['guest'])) {
	$mode = 'guest';
}
if (isset($this->httpRequest->input['chat'])) {
	$mode = 'chat';
}

$dataRoot->setAttribute('mode', $mode);

if (isset($this->httpRequest->input['xml'])) {
	$dataRoot->appendChild($retNode);
	$ret = \CMS\HTTPFile::createFromDocument($dataDoc, 'info.xml');
}

//my_dump(\FileSystem::drawBytes(realpath_cache_size()));

return $ret;