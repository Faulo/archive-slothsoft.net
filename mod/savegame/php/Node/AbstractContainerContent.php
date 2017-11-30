<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

abstract class AbstractContainerContent extends AbstractContentNode implements XmlBuildableInterface
{

    protected function loadContent(EditorElement $strucElement)
    {}
}
