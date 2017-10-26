<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class BinaryValue extends AbstractValueContent
{

    protected function decodeValue()
    {
        return $this->converter->decodeBinary($this->rawValue);
    }

    protected function encodeValue()
    {
        return $this->converter->encodeBinary($this->strucData['value']);
    }
}
