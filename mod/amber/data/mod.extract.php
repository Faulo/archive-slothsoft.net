<?php
namespace Slothsoft\CMS;

use Slothsoft\Amber\ModController;

$controller = new ModController(__DIR__ . '/..');

$result = $controller->extractAction($this->httpRequest);

return HTTPFile::createFromString($result);