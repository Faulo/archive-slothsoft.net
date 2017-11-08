<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class BinaryValue extends AbstractValueContent
{

    protected function decodeValue()
    {
        return $this->getConverter()->decodeBinary($this->rawValue);
    }

    protected function encodeValue()
    {
        return $this->getConverter()->encodeBinary($this->strucData['value']);
    }
}
