<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
use Traversable;
use DS\Vector;

declare(ticks = 1000);

class EventInstruction extends AbstractInstructionContent
{

    private $size;
    private $stepSize;
    
    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->size = $this->loadIntegerAttribute('size', 1);
        $this->stepSize = $this->loadIntegerAttribute('step-size', 1);
    }

    protected function loadInstruction()
    {
        $instructionList = [];
        
        for ($i = 0; $i < $this->size; $i += $this->stepSize) {
            $strucData = [];
            $strucData['position'] = $i;
            //$strucData['size'] = $this->stepSize;
            
            $instructionList[] = $this->getStrucElement()->clone(EditorElement::NODE_TYPES['event-step'], $strucData);
        }
        
        return count($instructionList)
            ? new Vector($instructionList)
               : null;
    }
}
