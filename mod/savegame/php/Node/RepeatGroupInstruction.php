<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class RepeatGroupInstruction extends AbstractInstructionContent
{

    public function __construct()
    {
        parent::__construct();
        $this->strucData['group-size'] = 1;
        $this->strucData['group-count'] = 1;
    }

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->strucData['group-size'] = $this->getParser()->evaluate($this->strucData['group-size'], $this->ownerFile);
        $this->strucData['group-count'] = $this->getParser()->evaluate($this->strucData['group-count'], $this->ownerFile);
    }

    protected function loadInstruction()
    {
        $this->instructionList = [];
        
        $start = 0;
        $step = $this->strucData['group-size'];
        $count = $this->strucData['group-count'] * $step;
        
        $positionList = [];
        for ($i = $start; $i < $count; $i += $step) {
            $positionList[] = $i;
        }
        
        foreach ($positionList as $i => $position) {
            $strucData = [];
            $strucData['position'] = $position;
            $strucData['name'] = $this->dictionary ? (string) $this->dictionary->getOption($i) : '';
            
            $this->instructionList[] = $this->getStrucElement()->clone('group', $strucData);
        }
    }
}

