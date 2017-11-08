<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class StringValue extends AbstractValueContent
{

    public function __construct()
    {
        parent::__construct();
        $this->strucData['encoding'] = '';
    }

    protected function decodeValue()
    {
        return $this->getConverter()->decodeString($this->rawValue, $this->strucData['size'], $this->strucData['encoding']);
    }

    protected function encodeValue()
    {
        return $this->getConverter()->encodeString($this->strucData['value'], $this->strucData['size'], $this->strucData['encoding']);
    }
}
