<?php
namespace Slothsoft\Savegame\Node;

use RangeException;
declare(ticks = 1000);

class EventDictionaryInstruction extends AbstractInstructionContent
{

    protected function loadInstruction()
    {
        $this->instructionList = [];
        
        $offsetWordSize = 2;
        $eventWordSize = 12;
        
        $eventCount = $this->ownerFile->extractContent($this->valueOffset, $offsetWordSize);
        $eventCount = $this->getConverter()->decodeInteger($eventCount, $offsetWordSize);
        
        if ($eventCount > 256) {
            throw new RangeException("there probably shouldn't be $eventCount events at $this->valueOffset in " . $this->ownerFile->getFileName());
        }
        
        $eventSizeList = [];
        $lastEnd = 0;
        for ($eventNo = 0; $eventNo < $eventCount; $eventNo ++) {
            $eventOffset = $this->valueOffset + 4 + $eventNo * $offsetWordSize;
            
            $eventEnd = $this->ownerFile->extractContent($eventOffset, $offsetWordSize);
            $eventEnd = $this->getConverter()->decodeInteger($eventEnd, $offsetWordSize);
            $eventEnd *= $eventWordSize;
            
            $eventSizeList[] = $eventEnd - $lastEnd;
            $lastEnd = $eventEnd;
        }
        $eventStartOffset = $this->valueOffset + 4 + $eventNo * $offsetWordSize;
        
        foreach ($eventSizeList as $i => $eventSize) {
            $strucData = [];
            $strucData['name'] = sprintf('event-%02d', $i + 1);
            $strucData['position'] = $eventStartOffset - $this->valueOffset;
            $strucData['size'] = $eventSize;
            $strucData['step-size'] = $eventWordSize;
            
            $this->instructionList[] = [
                'tagName' => 'event',
                'element' => $this->getStrucElement(),
                'strucData' => $strucData
            ];
            
            $eventStartOffset += $eventSize;
        }
    }
}