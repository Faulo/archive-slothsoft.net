<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
use DS\Vector;
use Traversable;

declare(ticks = 1000);

class RepeatGroupInstruction extends AbstractInstructionContent
{
    
    private $groupSize;
    private $groupCount;

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->groupSize = $this->loadIntegerAttribute('group-size');
        $this->groupCount = $this->loadIntegerAttribute('group-count');
    }

    protected function loadInstruction() 
    {
        $instructionList = [];
        
        $start = 0;
        $step = $this->groupSize;
        $count = $this->groupCount * $step;
        
        $positionList = [];
        for ($i = $start; $i < $count; $i += $step) {
            $positionList[] = $i;
        }
        
        foreach ($positionList as $i => $position) {
            $strucData = [];
            $strucData['position'] = $position;
            $strucData['name'] = $this->dictionary ? (string) $this->dictionary->getOption($i) : '';
            
            $instructionList[] = $this->getStrucElement()->clone(EditorElement::NODE_TYPES['group'], $strucData);
        }
        
        return count($instructionList)
        ? new Vector($instructionList)
        : null;
    }
}

