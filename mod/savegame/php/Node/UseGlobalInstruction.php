<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class UseGlobalInstruction extends AbstractInstructionContent
{

    public function __construct()
    {
        parent::__construct();
        $this->strucData['ref'] = '';
    }

    protected function loadInstruction()
    {
        $this->instructionList = [];
        
        if ($node = $this->ownerEditor->getGlobalById($this->strucData['ref'])) {
            $this->instructionList += $node->getStrucElementChildren();
        }
    }

    public function asXML()
    {
        return $this->getChildrenXML();
    }
}

