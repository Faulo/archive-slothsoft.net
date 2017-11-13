<?php
namespace Slothsoft\CMS;

use Slothsoft\Amber\SavegameController;
use Slothsoft\Core\DOMHelper;

$this->httpRequest->setInputValue('save', [
    'editor' => [
        'archives' => [
			'Icon_data.amb',
        ]
    ]
]);

$controller = new SavegameController(__DIR__);
$editor = $controller->loadEditor($this->httpRequest, $this);

$editorNode = $editor->asNode($dataDoc);

if ($this->httpRequest->hasInputValue('css')) {
	$xpath = DOMHelper::loadXPath($dataDoc);
	
	$ret = [];
	$fileNodeList = $xpath->evaluate('.//*[@file-name="Icon_data.amb"]/*', $editorNode);
    foreach ($fileNodeList as $fileNode) {
		$fileId = (int) $fileNode->getAttribute('file-name');
		
		$paletteCount = 50;
		$tilesetsDir = __DIR__ . '/../res/tilesets';
		
		$ret[] = sprintf(
			'*[data-tileset="%d"] *[data-picker-name="tile-id"]::after { background-image: url(/getResource.php/amber/tilesets/%d-%d); }',
			$fileId, $fileId, 0
		);
		for ($paletteId = 0; $paletteId < $paletteCount; $paletteId++) {
			if (file_exists(sprintf('%s/%d-%d.png', $tilesetsDir, $fileId, $paletteId))) {
				$ret[] = sprintf(
					'*[data-tileset-icon="%d"][data-palette="%d"] *[data-picker-name="tile-id"]::after { background-image: url(/getResource.php/amber/tilesets/%d-%d); }',
					$fileId, $paletteId, $fileId, $paletteId
				);
			}
		}
		$ret[] = '';
		
		$tileNodeList = $xpath->evaluate('.//*[@name="icons"]/*/*', $fileNode);
		foreach ($tileNodeList as $i => $tileNode) {
			$tileId = $i;
			$imageId = $xpath->evaluate('number(.//*[@name="image-id"]/@value)', $tileNode);
			
			$tileId++;
			$imageId--;
			
			$ret[] = sprintf('*[data-tileset-icon="%d"] *[data-picker-name="tile-id"][data-picker-value="%d"]::after { background-position-y: -%dem; }', $fileId, $tileId, $imageId);
		}
		
		$ret[] = '';
		$ret[] = '';
    }
	
	//$ret = array_unique($ret, SORT_STRING);
	
    return HTTPFile::createFromString(implode(PHP_EOL, $ret), 'tilesets.css');
}

return $editorNode;