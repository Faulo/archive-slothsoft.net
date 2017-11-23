<?php
namespace Slothsoft\CMS;

use Slothsoft\Amber\ModController;
$controller = new ModController(__DIR__ . '/..');

ini_set('memory_limit', '4G');

return $controller->editorAction($this->httpRequest)->asFile();