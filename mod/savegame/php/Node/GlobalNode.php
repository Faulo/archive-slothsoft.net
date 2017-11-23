<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class GlobalNode extends AbstractNode
{
    private $globalId;
    
    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->globalId = $this->loadStringAttribute('global-id');
    }

    protected function loadNode()
    {}

    protected function loadChildren()
    {}

    public function asXML() : string
    {
        return $this->getXmlContent();
    }

    public function getGlobalId() : string
    {
        return $this->globalId;
    }
}
