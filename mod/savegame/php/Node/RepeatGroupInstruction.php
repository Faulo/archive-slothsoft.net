<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class RepeatGroupInstruction extends AbstractInstructionContent
{

    public function __construct()
    {
        parent::__construct();
        $this->strucData['group-count'] = '1';
        $this->strucData['group-size'] = '1';
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        $this->strucData['group-size'] = $this->parseInt($this->strucData['group-size']);
        $this->strucData['group-count'] = $this->parseInt($this->strucData['group-count']);
    }

    protected function loadInstruction()
    {
        $this->instructionElements = [];
        
        $start = 0;
        $step = $this->strucData['group-size'];
        $count = $this->strucData['group-count'] * $step;
        $positionList = [];
        for ($i = $start; $i < $count; $i += $step) {
            $positionList[] = $i;
        }
        
        $parentNode = $this->createInstructionContainer();
        
        foreach ($positionList as $i => $position) {
            $instruction = [];
            $instruction['position'] = $position;
            if (isset($this->dictionaryOptions[$i])) {
                $instruction['name'] = $this->dictionaryOptions[$i];
            }
            $instructionElement = $this->createInstructionElement('group', $instruction);
            if ($instructionContent = $this->getInstructionContent()) {
                $instructionElement->appendChild($instructionContent);
            }
            $parentNode->appendChild($instructionElement);
        }
        
        $this->instructionElements[] = $parentNode;
    }
}

