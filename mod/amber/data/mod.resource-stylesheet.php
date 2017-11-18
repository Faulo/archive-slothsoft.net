<?php
namespace Slothsoft\CMS;

use Slothsoft\Amber\ModController;
$controller = new ModController(__DIR__ . '/..');
//my_dump($this->httpRequest->getInputValue('id'));
$this->httpResponse->styleFiles[] = $controller->resourceAction($this->httpRequest)->getPath();
//my_dump($this->httpResponse->styleFiles);