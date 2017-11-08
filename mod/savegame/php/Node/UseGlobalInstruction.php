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
            foreach ($node->getStrucElementChildren() as $childNode) {
                $this->instructionList[] = [
                    'tagName' => $childNode->localName,
                    'element' => $childNode,
                    'strucData' => []
                ];
            }
        }
    }

    public function asXML()
    {
        return $this->getChildrenXML();
    }
}

