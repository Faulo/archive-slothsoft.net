<?php
namespace Slothsoft\CMS;

use Slothsoft\Amber\SavegameController;
$controller = new SavegameController(__DIR__);
$editor = $controller->loadEditor($this->httpRequest, $this);

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
    if ($saveFile) {
        $editor->writeArchiveFile($saveFile);
    }
    if ($downloadFile) {
        $this->httpResponse->setDownload(true);
        return $editor->getArchiveFile($downloadFile);
    }
}

return $editor->asNode($dataDoc);