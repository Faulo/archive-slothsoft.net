<?php
namespace Slothsoft\CMS;

// *
use Slothsoft\CMS\Tracking\Manager;


$archive = Manager::getArchive();
$archive->install();
// $res = $archive->import();
if (! $this->httpRequest->getInputValue('backup', 1)) {
    $res = $archive->backup();
}
if (! $this->httpRequest->getInputValue('parse', 1)) {
    $res = $archive->parse();
}

$view = Manager::getView();
$view->parseRequest($this->httpRequest);

return $view->asNode($dataDoc);