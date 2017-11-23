<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class EventScriptValue extends AbstractValueContent
{

    protected function loadContent()
    {
        $scriptSize = 4;
        
        $offsetWordSize = 2;
        $eventWordSize = 12;
        
        $eventCount = $this->getOwnerFile()->extractContent($this->valueOffset, $offsetWordSize);
        $eventCount = $this->getConverter()->decodeInteger($eventCount, $offsetWordSize);
        
        $lastEnd = 0;
        $eventSizeOffset = $this->valueOffset + 4;
        for ($eventNo = 0; $eventNo < $eventCount; $eventNo ++) {
            $eventEnd = $this->getOwnerFile()->extractContent($eventSizeOffset, $offsetWordSize);
            $eventEnd = $this->getConverter()->decodeInteger($eventEnd, $offsetWordSize);
            $eventEnd *= $eventWordSize;
            
            $scriptSize += $offsetWordSize;
            $scriptSize += $eventEnd - $lastEnd;
            
            $lastEnd = $eventEnd;
            $eventSizeOffset += $offsetWordSize;
        }
        
        $value = $this->getOwnerFile()->extractContent($this->valueOffset, $scriptSize);
        $this->setRawValue($value);
    }

    protected function decodeValue()
    {
        return $this->getConverter()->decodeScript($this->rawValue);
    }

    protected function encodeValue()
    {
        return $this->getConverter()->encodeScript($this->value);
    }
}