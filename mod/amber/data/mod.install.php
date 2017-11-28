<?php
namespace Slothsoft\CMS;

use Slothsoft\Amber\ModController;
$controller = new ModController(__DIR__ . '/..');

ini_set('memory_limit', '2G');

return $controller->installAction($this->httpRequest);