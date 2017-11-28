<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
declare(ticks = 1000);

class UseGlobalInstruction extends AbstractInstructionContent
{

    private $globalRef;

    protected function getXmlInstructionType(): string
    {
        return 'use-global';
    }

    protected function loadStruc(EditorElement $strucElement)
    {
        parent::loadStruc($strucElement);
        
        $this->globalRef = (string) $strucElement->getAttribute('ref');
    }

    protected function loadInstruction(EditorElement $strucElement)
    {
        return $this->getOwnerSavegame()->getGlobalElementsById($this->globalRef);
    }

    public function asXML(): string
    {
        return $this->getXmlContent();
    }
}

