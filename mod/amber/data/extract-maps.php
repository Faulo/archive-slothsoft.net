<?php
namespace Slothsoft\CMS;

use Savegame\Editor;
use Savegame\Dictionary;
use Slothsoft\Core\DOMHelper;

$mode = $this->httpRequest->getInputValue('SaveDefault', 'thalion');
$mode = preg_replace('~[^\w]~', '', $mode);
$name = $this->httpRequest->getInputValue('SaveName', null);
$name = preg_replace('~[^\w]~', '', $name);

$defaultDir = realpath(__DIR__ . '/../res/save/default');
$tempDir = realpath(__DIR__ . '/../res/save/temp');

$editorFile = $this->getResourcePath('/amber/AmbermoonAmberfiles2');

$editorConfig = [];
$editorConfig['defaultDir'] = $defaultDir;
$editorConfig['tempDir'] = $tempDir;
$editorConfig['mode'] = $mode;
$editorConfig['id'] = $name;
$editorConfig['ambtoolPath'] = 'D:\\www\\mod\\amber\\cli\\ambtool.exe';
$editorConfig['selectedArchives'] = [];
$editorConfig['uploadedArchives'] = [];

$editorConfig['selectedArchives']['1Map_texts.amb'] = true;
$editorConfig['selectedArchives']['2Map_texts.amb'] = true;
$editorConfig['selectedArchives']['3Map_texts.amb'] = true;

$editor = new Editor($editorConfig);

$editor->load($editorFile);

return $editor->asNode($dataDoc);