<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Savegame\EditorElement;
use Traversable;
use DS\Vector;

declare(ticks = 1000);

class BitFieldInstruction extends AbstractInstructionContent
{
    private $size;
    private $firstBit;
    private $lastBit;

    protected function loadStruc()
    {
        parent::loadStruc();
        
        $this->size = $this->loadIntegerAttribute('size', 1);
        $this->firstBit = $this->loadIntegerAttribute('first-bit', 0);
        $this->lastBit = $this->loadIntegerAttribute('last-bit', $this->size * 8 - 1);
    }

    protected function loadInstruction() 
    {
        $instructionList = [];
        
        $max = $this->size - 1;
        for ($i = $this->firstBit; $i <= $this->lastBit; $i ++) {
            $offset = (int) ($i / 8);
            $pos = $max - $offset;
            $bit = $i - 8 * $offset;
            
            $strucData = [];
            $strucData['position'] = $pos;
            $strucData['bit'] = $bit;
            $strucData['size'] = 1;
            $strucData['name'] = $this->dictionary ? (string) $this->dictionary->getOption($i) : '';
            
            $instructionList[] = $this->getStrucElement()->clone(EditorElement::NODE_TYPES['bit'], $strucData);
        }
        return count($instructionList)
            ? new Vector($instructionList)
        : null;
    }
}