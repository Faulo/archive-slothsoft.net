<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class EventDictionaryInstruction extends AbstractInstructionContent
{

    protected function loadInstruction()
    {
        $offsetWordSize = 2;
        $eventWordSize = 12;
        
        $this->instructionElements = [];
        
        $parentNode = $this->createInstructionContainer();
        
        $eventCount = $this->ownerFile->extractContent($this->valueOffset, $offsetWordSize);
        $eventCount = $this->converter->decodeInteger($eventCount, $offsetWordSize);
        
        $eventSizeList = [];
        $lastEnd = 0;
        for ($eventNo = 0; $eventNo < $eventCount; $eventNo ++) {
            $eventOffset = $this->valueOffset + 4 + $eventNo * $offsetWordSize;
            
            $eventEnd = $this->ownerFile->extractContent($eventOffset, $offsetWordSize);
            $eventEnd = $this->converter->decodeInteger($eventEnd, $offsetWordSize);
            $eventEnd *= $eventWordSize;
            
            $eventSizeList[] = $eventEnd - $lastEnd;
            $lastEnd = $eventEnd;
        }
        $eventStartOffset = $this->valueOffset + 4 + $eventNo * $offsetWordSize;
        
        foreach ($eventSizeList as $i => $eventSize) {
            $instruction = [];
            $instruction['name'] = sprintf('event-%02d', $i + 1);
            $instruction['position'] = $eventStartOffset - $this->valueOffset;
            $instruction['size'] = $eventSize;
            $instruction['step-size'] = $eventWordSize;
            
            $parentNode->appendChild($this->createInstructionElement('event', $instruction));
            
            $eventStartOffset += $eventSize;
        }
        $this->instructionElements[] = $parentNode;
    }
}