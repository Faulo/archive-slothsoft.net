<?php
namespace Slothsoft\CMS;

use Slothsoft\Amber\SavegameController;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\Image;
$this->httpRequest->setInputValue('save', [
    'editor' => [
        'archives' => [
            'Monster_char_data.amb'
        ]
    ]
]);

$controller = new SavegameController(__DIR__);
$editor = $controller->loadEditor($this->httpRequest, $this);

$editorNode = $editor->asNode($dataDoc);

if ($this->httpRequest->hasInputValue('cron')) {
    $sourceDir = realpath(__DIR__ . '/../res/monster-gfx-raw');
    assert($sourceDir !== false, 'source dir must exist');
    
    $targetDir = realpath(__DIR__ . '/../res/monster-gfx');
    assert($targetDir !== false, 'target dir must exist');
    
    $xpath = DOMHelper::loadXPath($dataDoc);
    
    $monsterNodeList = $xpath->evaluate('.//*[@file-name = "Monster_char_data.amb"]/*', $editorNode);
    
    foreach ($monsterNodeList as $monsterNode) {
        $spriteId = $xpath->evaluate('number(.//*[@name = "gfx-id"]/@value)', $monsterNode);
        $spriteWidth = $xpath->evaluate('number(.//*[@name = "gfx-width"]/*[@name = "source"]/@value)', $monsterNode);
        $spriteHeight = $xpath->evaluate('number(.//*[@name = "gfx-height"]/*[@name = "source"]/@value)', $monsterNode);
        
        $targetWidth = $xpath->evaluate('number(.//*[@name = "gfx-width"]/*[@name = "target"]/@value)', $monsterNode);
        $targetHeight = $xpath->evaluate('number(.//*[@name = "gfx-height"]/*[@name = "target"]/@value)', $monsterNode);
        
        $spriteFile = sprintf('%03d', $spriteId);
        $tgaFile = sprintf('%d.tga', $spriteId);
        $pngFile = sprintf('%d.png', $spriteId);
        $targetFile = sprintf('%d.%dx%d.png', $spriteId, $targetWidth, $targetHeight);
        
        $spritePath = $sourceDir . DIRECTORY_SEPARATOR . $spriteFile;
        $tgaPath = $targetDir . DIRECTORY_SEPARATOR . $tgaFile;
        $pngPath = $targetDir . DIRECTORY_SEPARATOR . $pngFile;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $targetFile;
        
        $spriteBitplanes = 5;
        $spritePalette = 15;
        
        if (! file_exists($tgaPath)) {
            assert(file_exists($spritePath), "sprite file $spritePath must exist");
            
            $cmd = sprintf('%s %s -out %s -w %d -b %d -p %d', $editor->getConfigValue('ambgfxPath'), escapeshellarg($spritePath), escapeshellarg($tgaPath), $spriteWidth, $spriteBitplanes, $spritePalette);
            exec($cmd);
        }
        if (! file_exists($pngPath)) {
            assert(file_exists($tgaPath), "tga file $tgaPath must exist");
            
            Image::convertFile($tgaPath, $pngPath);
        }
        if (! file_exists($targetPath)) {
            assert(file_exists($pngPath), "png file $pngPath must exist");
            
            $image = Image::imageInfo($pngPath);
            
            Image::scaleFile($pngPath, $targetPath, $targetWidth, round($targetHeight * $image['height'] / $spriteHeight, 0));
        }
    }
}

return $editorNode;