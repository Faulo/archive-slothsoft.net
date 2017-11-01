<?php
namespace Slothsoft\CMS;

use Slothsoft\Amber\SavegameController;
$this->httpRequest->setInputValue('save', [
    'editor' => [
        'archives' => [
            '1Map_texts.amb',
            '2Map_texts.amb',
            '3Map_texts.amb',
        ]
    ]
]);

$controller = new SavegameController(__DIR__);
$editor = $controller->loadEditor($this->httpRequest, $this);

$editorNode = $editor->asNode($dataDoc);

return $editorNode;