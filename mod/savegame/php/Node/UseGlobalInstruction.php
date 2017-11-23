<?php
namespace Slothsoft\Savegame\Node;


declare(ticks = 1000);

class UseGlobalInstruction extends AbstractInstructionContent
{
    private $globalRef;
    
    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->globalRef = $this->loadStringAttribute('ref');
    }

    protected function loadInstruction()
    {
        $ret = null;
        if ($node = $this->getOwnerEditor()->getGlobalById($this->globalRef)) {
            $ret = $node->getStrucElementChildren();
        }
        return $ret;
    }

    public function asXML() : string
    {
        return $this->getXmlContent();
    }
}

