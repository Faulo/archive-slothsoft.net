<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class GlobalNode extends AbstractNode
{

    private $globalId;

    protected function getXmlAttributes(): string
    {
        return '';
    }

    protected function getXmlTag(): string
    {
        return 'global';
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->globalId = (string) $strucElement->getAttribute('global-id');
    }

    protected function loadNode(EditorElement $strucElement)
    {}

    protected function loadChildren(EditorElement $strucElement)
    {}

    public function asXML(): string
    {
        return $this->getXmlContent();
    }

    public function getGlobalId(): string
    {
        return $this->globalId;
    }
}
