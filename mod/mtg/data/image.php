<?php

$card = [];
$card['color'] = $this->httpRequest->getInputValue('color');

$path = \Slothsoft\MTG\OracleInfo::getColorPath($card);
if ($path = realpath($path)) {
	return \Slothsoft\CMS\HTTPFile::createFromPath($path);
}

return;









$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

//$idTable = $oracle->getIdTable();
//$xmlTable = $oracle->getXMLTable();

$dir = dirname(__FILE__) . '/../res/images';

$set = $this->httpRequest->getInputValue('set');
$no = $this->httpRequest->getInputValue('no');
$id = $this->httpRequest->getInputValue('id');
$rarity = $this->httpRequest->getInputValue('rarity');
$color = $this->httpRequest->getInputValue('color');
$recreate = isset($this->httpRequest->input['recreate']);

$ret = null;
try {
	if ($id) {
		if ($set === '') {
			$set = '_';
		}
		if ($no === '') {
			$no = '0';
		}
		if ($image = $oracle->getCardImage($dir, $id, $set, $no)) {
			$ret = $image->getFile($recreate);
		}
	} elseif ($rarity) {
		if ($image = $oracle->getRarityImage($dir, $set, $rarity)) {
			$ret = $image->getFile($recreate);
		}
	} elseif ($set) {
		if ($image = $oracle->getSetImage($dir, $set)) {
			$ret = $image->getFile($recreate);
		}
	} elseif ($color) {
		if ($image = $oracle->getColorImage($dir, $color)) {
			$ret = $image->getFile($recreate);
		}
	} else {
		$thumbWidth = 32*10;
		$thumbWidth = 208*2;
		$thumbHeight = 12*14;
		$thumbHeight = 111*2;
		$widthCount = 3;
		$heightCount = 33;
		
		$minCount = 100;
		
		$fileName = '_mtg.png';
		$filePath = $dir . DIRECTORY_SEPARATOR . $fileName;
			
		$dbmsTable = \DBMS\Manager::getTable('mtg', 'oracle-ids');
		$setList = [];
		$resList = $dbmsTable->select('DISTINCT expansion_abbr');
		foreach ($resList as $res) {
			if ($res) {
				$countList = $dbmsTable->select('COUNT(*)', ['expansion_abbr' => $res]);
				if ($count = (int) current($countList) and $count > $minCount) {
					$idList = $dbmsTable->select('oracle_id', ['expansion_abbr' => $res], 'ORDER BY oracle_id LIMIT 1');
					if ($id = current($idList)) {
						$setList[(int) $id] = $res;
					}
				}
			}
		}
		ksort($setList);
		
		//my_dump($dbmsTable->select('count(*)', ['expansion_abbr' => $setList]));die();
		
		$imageList = [];
		$i = 0;
		foreach ($setList as $set) {
			$image = sprintf('%s/_set.%s.png', $dir, $set);
			if ($image = realpath($image)) {
				if ($image = \Image::generateThumbnail($image, $thumbWidth, $thumbHeight, false)) {
					$imageList[$i] = $image;
					$i++;
				}
			}
		}
		\Image::createSprite($filePath, $thumbWidth, $thumbHeight, $widthCount, $heightCount, $imageList);
		$ret = \Slothsoft\CMS\HTTPFile::createFromPath($filePath, $fileName);
	}
} catch (\Exception $e) {
	
}

return $ret;