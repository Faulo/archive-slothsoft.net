<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class EventScriptValue extends AbstractValueContent {
	protected function loadContent() {
		$scriptSize = 4;
		
		$offsetWordSize = 2;
		$eventWordSize = 12;
		
		$eventCount = $this->ownerFile->extractContent($this->valueOffset, $offsetWordSize);
		$eventCount = $this->converter->decodeInteger($eventCount, $offsetWordSize);
		
		$lastEnd = 0;
		$eventSizeOffset = $this->valueOffset + 4;
		for ($eventNo = 0; $eventNo < $eventCount; $eventNo++) {
			$eventEnd = $this->ownerFile->extractContent($eventSizeOffset, $offsetWordSize);
			$eventEnd = $this->converter->decodeInteger($eventEnd, $offsetWordSize);
			$eventEnd *= $eventWordSize;
			
			$scriptSize += $offsetWordSize;
			$scriptSize += $eventEnd - $lastEnd;
			
			$lastEnd = $eventEnd;
			$eventSizeOffset += $offsetWordSize;
		}
		
		$value = $this->ownerFile->extractContent($this->valueOffset, $scriptSize);
		$this->setRawValue($value);
	}
	protected function decodeValue() {
		return $this->converter->decodeScript($this->rawValue);
	}
	protected function encodeValue() {
		return $this->converter->encodeScript($this->strucData['value']);
	}
}