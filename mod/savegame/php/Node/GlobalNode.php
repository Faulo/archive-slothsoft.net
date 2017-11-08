<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class GlobalNode extends AbstractNode
{

    public function __construct()
    {
        parent::__construct();
        $this->strucData['global-id'] = '';
    }

    protected function loadNode()
    {}

    protected function loadChildren()
    {}

    public function asXML()
    {
        return $this->getChildrenXML();
    }

    public function getGlobalId()
    {
        return $this->strucData['global-id'];
    }
}
