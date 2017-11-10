<?php
namespace Slothsoft\CMS;

use Slothsoft\Amber\ResourceController;

$controller = new ResourceController(__DIR__ . '/..');

return $controller->resourceAction($this->httpRequest);