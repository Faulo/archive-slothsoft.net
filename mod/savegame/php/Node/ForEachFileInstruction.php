<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class ForEachFileInstruction extends AbstractInstructionContent
{

    public function __construct()
    {
        parent::__construct();
        $this->strucData['count'] = '1';
        $this->strucData['step'] = '1';
        $this->strucData['steps'] = '';
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        $this->strucData['step'] = $this->parseInt($this->strucData['step']);
        $this->strucData['count'] = $this->parseInt($this->strucData['count']);
    }

    protected function loadInstruction()
    {
        $this->instructionElements = [];
        
        foreach ($this->ownerArchive->getFileList() as $filepath) {
            $instruction = [];
            $instruction['file-name'] = basename($filepath);
            $instruction['file-path'] = $filepath;
            $instruction['position'] = $this->strucData['position'];
            
            $instructionElement = $this->createInstructionElement('file', $instruction);
            foreach ($this->getStrucElementChildren() as $node) {
                $instructionElement->appendChild($node->cloneNode(true));
            }
            $this->instructionElements[] = $instructionElement;
        }
    }
}

