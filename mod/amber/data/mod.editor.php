<?php
namespace Slothsoft\CMS;

use Slothsoft\Amber\ModController;

$controller = new ModController(__DIR__ . '/..');

$editor = $controller->editorAction($this->httpRequest);

$saveAll = $this->httpRequest->hasInputValue('SaveAll');
$downloadAll = $this->httpRequest->hasInputValue('DownloadAll');

$saveFile = $this->httpRequest->getInputValue('SaveFile', null);
$downloadFile = $this->httpRequest->getInputValue('DownloadFile', null);

if ($saveAll or $downloadAll) {
    if ($saveAll) {
        // todo
    }
    if ($downloadAll) {
        $this->httpResponse->setDownload(true);
        return $editor->asFile();
    }
}

if ($saveFile or $downloadFile) {
    $editor->updateContent();
    if ($saveFile) {
        $editor->writeArchiveFile($saveFile);
    }
    if ($downloadFile) {
        $this->httpResponse->setDownload(true);
        return $editor->getArchiveFile($downloadFile);
    }
}

return $editor->asNode($dataDoc);