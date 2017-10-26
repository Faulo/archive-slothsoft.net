<?php
namespace Slothsoft\CMS;

use Slothsoft\Amber\SavegameController;

$this->httpRequest->setInputValue('save', ['editor' => [ 'archives' => ['AM2_BLIT']]]);

$controller = new SavegameController(__DIR__);
$editor = $controller->loadEditor($this->httpRequest, $this);

$editorNode = $editor->asNode($dataDoc);

return $editorNode;